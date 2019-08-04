<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpClient\HttpClient;
use Aws\Ses\SesClient;
use Vimeo\Vimeo;

class ApiController extends AbstractController
{
    private $tmdb;
    private $vimeoClientId;
    private $vimeoClientSecret;
    private $vimeoAccessToken;

    public function __construct($tmdb, $vimeoClientId, $vimeoClientSecret, $vimeoAccessToken)
    {
        $this->tmdb = $tmdb;
        $this->vimeoClientId = $vimeoClientId;
        $this->vimeoClientSecret = $vimeoClientSecret;
        $this->vimeoAccessToken = $vimeoAccessToken;
    }

    /**
     * @Route("/api/trailer/{movie}", methods={"GET"})
     */
    public function imdb($movie)
    {
        $cache = new FilesystemAdapter();
        $data = $cache->getItem($movie);

        if (!$data->isHit()) {
            // ... item does not exist in the cache
            $httpClient = HttpClient::create();
            $response = $httpClient->request('GET', $this->tmdb.$movie);
            $contentArr = $response->toArray();

            $json = $this->addVimeoTrailers($movie, $contentArr);

            $data->set($json);
            $cache->save($data);
        }
        // retrieve the value stored by the cache
        $json = $data->get();

        return new JsonResponse($json);
    }

    protected function addVimeoTrailers($movie, $contentArr)
    {
        $client = new Vimeo($this->vimeoClientId, $this->vimeoClientSecret, $this->vimeoAccessToken);

        foreach ($contentArr['results'] as $key => $content) {
            $releasedate = explode('-', $content['release_date']);
            $releaseYear = reset($releasedate);
            $response = $client->request('/videos', array('query' => $content['title'], 'per_page' => 1, 'sort' => 'relevant'), 'GET');

            try {
                $videoId = explode('/', reset($response['body']['data'])['uri']);
                $contentArr['results'][$key]['vimeo']['id'] = end($videoId);
                $contentArr['results'][$key]['vimeo']['name'] = reset($response['body']['data'])['name'];
                $contentArr['results'][$key]['vimeo']['duration'] = reset($response['body']['data'])['duration'];
                $contentArr['results'][$key]['vimeo']['link'] = reset($response['body']['data'])['link'];
                $contentArr['results'][$key]['vimeo']['description'] = reset($response['body']['data'])['description'];
                $contentArr['results'][$key]['vimeo']['user'] = reset($response['body']['data'])['user']['name'];
            } catch (Exception $e) {
                echo 'Message: '.$e->getMessage();
            }
        }

        return $contentArr['results'];
    }

    /**
     * @Route("/api/send/movie", methods={"POST"})
     */
    public function send(Request $request)
    {
    	$movie = $request->get('movie');
    	$email = $request->get("email");

        $listOfVideos = $this->imdb($movie);
        $resultsArray = json_decode($listOfVideos->getContent(), true);
        $html_body = $this->renderView('result.html.twig', ['results' => $resultsArray]);

        $configuration_set = 'DEPT';
        $subject = 'IMDB about '.$movie;

        $key = getenv('AWS_KEY'); 
        $secret = getenv('AWS_SECRET'); 

        // send email using AWS SES
        $SesClient = new SesClient([
            'region' => 'eu-west-1',
            'version' => '2010-12-01', //'latest'
            'credentials' => [
                'key' => $key,
                'secret' => $secret,
            ],
        ]);

        try {
            $result = $SesClient->sendEmail([
            'Destination' => [
                'ToAddresses' => [$email],
            ],
            'ReplyToAddresses' => [$email],
            'Source' => 'kareem.ashraf.91@gmail.com',
            'Message' => [
                'Body' => [
                    'Html' => [
                        'Charset' => 'UTF-8',
                        'Data' => $html_body,
                    ],
                    'Text' => [
                        'Charset' => 'UTF-8',
                        'Data' => $listOfVideos,
                    ],
                ],
                'Subject' => [
                    'Charset' => 'UTF-8',
                    'Data' => $subject,
                ],
            ],
            'ConfigurationSetName' => $configuration_set,
        ]);
        $messageId = $result['MessageId'];
        $messageStatus = $result['@metadata']['statusCode'];

            return new Response($messageStatus);
        } catch (AwsException $e) {
            die($e->getMessage());
        }
        //end sending the email
    }
}
