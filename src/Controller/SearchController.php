<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpClient\HttpClient;
use Vimeo\Vimeo;

class SearchController extends AbstractController
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
      *
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
			$releasedate = explode("-", $content['release_date']);
			$releaseYear = reset($releasedate);
			$response = $client->request('/videos', array('query'=>$content['title'], 'per_page'=>1, 'sort'=> 'relevant'), 'GET');

			try{
				$videoId = explode('/', reset($response['body']['data'])['uri']);
				$contentArr['results'][$key]['vimeo']['id'] = end($videoId);
				$contentArr['results'][$key]['vimeo']['name'] = reset($response['body']['data'])['name'];
				$contentArr['results'][$key]['vimeo']['duration'] = reset($response['body']['data'])['duration'];
				$contentArr['results'][$key]['vimeo']['link'] = reset($response['body']['data'])['link'];
				$contentArr['results'][$key]['vimeo']['description'] = reset($response['body']['data'])['description'];
				$contentArr['results'][$key]['vimeo']['user'] = reset($response['body']['data'])['user']['name'];
			}
			catch(Exception $e) {
			  echo 'Message: ' .$e->getMessage();
			}	
		}
		return $contentArr['results'];

    }
}