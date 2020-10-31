<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * Source de données statique
     * Une liste d'auteurs et de leurs œuvres
     */
    const AUTHORS = [
        [ 'name' => 'Victor Hugo', 'works' => [
            ['title' => 'Notre-Dame de Paris', "category" => 'roman'],
            ['title' => 'Les Misérables', "category" => 'roman'],
            ['title' => 'Les Contemplations', "category" => 'poésie']
        ]
        ],
        [ 'name' => 'Thomas Mann', 'works' => [
            ['title' => 'Doktor Faustus', "category" => 'roman'],
            ['title' => 'Les Buddenbrock', "category" => 'roman']
        ]
        ],
        [ 'name' => 'William Shakespeare', 'works' => [
            ['title' => 'Le roi Lear', "category" => 'théâtre'],
            ['title' => 'Les Sonnets', "category" => 'poésie']
        ]
        ],
        [ 'name' => 'François Rabelais', 'works' => [
            ['title' => 'Le Tiers-Livre', "category" => 'roman'],
            ['title' => 'Le Quart-Livre', "category" => 'roman']
        ]
        ],
        [ 'name' => 'Platon', 'works' => [
            ['title' => 'La République', "category" => 'essai'],
            ['title' => 'Le Timée', "category" => 'essai']
        ]
        ],
    ];
    /**
     * @Route("/default", name="default")
     */
    public function index(): Response
    {
        return $this->render('default/index.html.twig', [
            /*
             *  Le tableau est envoyé à Twig pour affichage.
             *  Dans Twig, la variable porter le nom de la clef dans le tableau (authors)
             */
            'authors' => self::AUTHORS,
        ]);
    }
}
