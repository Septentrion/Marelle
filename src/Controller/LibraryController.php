<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class LibraryController extends AbstractController
{

    private function select(array $data, string $letter)
    {
	    return array_flip(
		      array_filter(
			    array_flip($data), 
			    function($var) use ($letter) { return substr($var, 0, 1) == $letter; }
	    ));
    }

    /**
     * @Route("/home/ecrivains/{limit}", name="home", requirements={"limit"="[0-9]+"})
     * @Template("pages/home.html.twig")
     */
    public function index(Request $request, int $limit = 3)
    {
	    $i = $request->query->get('initiale', 'A');
	    // $cookies = $request->cookies;
	    // $headers = $request->headers->get('Content-Type');

	    $content = [
		    'Victor Hugo' => 'Notre-Dame de Paris', 
		    'Albert Camus' => 'L‘étranger', 
		    'Mme de Lafayette' => 'La Princesse de Clèves', 
		    'Denis Diderot' => 'Jacques le Fataliste',
		    'Nathalie Sarraute' => 'Pour un oui ou pour un non',
		    'Stendhal' => 'Le Rouge et le Noir'
	    ];

	    $selection = $this->select($content, $i);

	    return [
		    'bookList' => array_splice($selection, 0, $limit),
		    'controller_name' => 'HomeController',
		    'displayFooter' => true,
	    	    'initiale' => $i
	    ];
    }

    public function jsonIndex(Request $request, int $limit = 3)
    {
	    $i = $request->query->get('initiale', 'A');

	    $content = [];

	    $selection = array_splice($this->select($content, $i), 0 , $limit);

	    // return (new JSONReponse($selection))->headers->('Content-Type' => 'application/json');;
	    return $this->json(json_encode($selection));

	    // return $this->file('test.png');
    }

    public function about() {
    }
}
