<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class IndexController extends ApiController
{

	/**
      * @Route("/", name="Homepage")
      *
     */
    public function index()
    {
    	return $this->render('index.html.twig');
    }

    /**
      * @Route("/search", methods={"POST"})
      *
     */
    public function search(Request $request)
    {
    	$searchValue = $request->request->get("search");
    	$result = $this->imdb($searchValue);

    	$resultsArray = json_decode($result->getContent(), true);

    	return $this->render('result.html.twig', ["results" => $resultsArray ]);

    }


    
}