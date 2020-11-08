<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class RequestsController extends AbstractController
{
    /**
     * Source de données statique
     * Une liste d'auteurs et de leurs œuvres
     */
    const AUTHORS = [
        [ 'name' => 'Victor Hugo', 'lang' => 'fr', 'works' => [
            ['title' => 'Notre-Dame de Paris', "category" => 'roman'],
            ['title' => 'Les Misérables', "category" => 'roman'],
            ['title' => 'Les Contemplations', "category" => 'poésie']
        ]
        ],
        [ 'name' => 'Thomas Mann', 'lang' => 'de', 'works' => [
            ['title' => 'Doktor Faustus', "category" => 'roman'],
            ['title' => 'Les Buddenbrock', "category" => 'roman']
        ]
        ],
        [ 'name' => 'William Shakespeare', 'lang' => 'en', 'works' => [
            ['title' => 'Le roi Lear', "category" => 'théâtre'],
            ['title' => 'Les Sonnets', "category" => 'poésie']
        ]
        ],
        [ 'name' => 'François Rabelais', 'lang' => 'fr', 'works' => [
            ['title' => 'Le Tiers-Livre', "category" => 'roman'],
            ['title' => 'Le Quart-Livre', "category" => 'roman']
        ]
        ],
        [ 'name' => 'Platon', 'lang' => 'el', 'works' => [
            ['title' => 'La République', "category" => 'essai'],
            ['title' => 'Le Timée', "category" => 'essai']
        ]
        ],
        [ 'name' => 'Virginia Woolf', 'lang' => 'en', 'works' => [
            ['title' => 'Mrs Dalloway', "category" => 'théâtre'],
        ]
        ],
    ];

    /**
     * Affiche la liste des auteurs
     *
     * @Template("default/index.html.twig")
     * @Route("/authors", name="authors_list")
     */
    public function index(): array
    {
        // return $this->render('default/index.html.twig', [
        return [
            /*
             *  Le tableau est envoyé à Twig pour affichage.
             *  Dans Twig, la variable porter le nom de la clef dans le tableau (authors)
             */
            'authors' => self::AUTHORS,
        ];
    }


    /**
     * Affiche les données concernant un auteur
     *
     *
     * @Route("/author/{id}",  
     *     name="show_author",
     *     methods={"GET"},
     *     requirements={"id": "[0-9]+"},
     *     defaults={"id": 0}
     *     )
     * @Template("default/author.html.twig")
     */
    public function author(int $id = 0): array
    {
        return [
            /*
             *  Grâce à l'annotation @Template, la méthode n'a qu'à renvoyer le tableau des variables.
             */
            'author' => self::AUTHORS[$id],
        ];
    }

    /**
     * Alias de la méthode `show` par l'intermédiaire d'une redirection HTTP
     *
     * @param integer $id Un indice du tableau des auteurs
     * @return Response
     *
     * @Route("/author/{id}/redirect",
     *     name="redirect_to_author",
     *     methods={"GET"},
     *     requirements={"id": "[0-9]+"},
     *     defaults={"id": 0}
     *     )
     */
    public function redirectAuthor(int $id = 0): Response
    {
        return $this->redirectToRoute('show_author', ['id' => $id]);
    }

    /**
     * Envoie une réponse dans le format choisi
     * On remarque que le schéma de la routes est une variante de la précédente,
     *   mais il n'y pas d'ambiguité puisque le variable `_format` ne peut pas être vide
     * Dans une application réelle, on chercherait plutôt à inspecter la valeur de l'entête HTTP “Accept”
     *
     * @param string $_format Le format de la réponse
     * @param integer $id Un indice du tableau des auteurs
     * @return Response
     *
     * @see https://symfony.com/doc/current/components/serializer.html#the-xmlencoder
     *
     * @Route("/author/{id}/format/{_format}",
     *     name="negociating_author",
     *     methods={"GET"},
     *     requirements={"id": "[0-9]+", "_format": "html|json|xml"},
     *     defaults={"id": 0}
     *     )
     */
    public function negociatingAuthor(string $_format, int $id = 0): Response
    {
        switch ($_format) {
            case 'xml':
                /*
                 * Dans ce cas précis, nous allons envoyer une réponse par défaut
                 */
                $response = new Response();
                /*
                 * XmlEncoder est une classe du composant `Serializer` dont le rôle est de
                 *   linéariser et délinéariser les objets PHP.
                 */
                $encoder = new XmlEncoder();
                /*
                 * Nous adaptons ici l'entête HTTP qui permettra au navigateur (au client) de comprendre
                 * le format de la réponse.
                 */
                $response->headers->set("Content-Type", "application/xml");
                /*
                 * setContent charge le corps de la réponse
                 */
                $response->setContent($encoder->encode(self::AUTHORS[$id], 'xml'));
                return $response;
            case 'json':
                /*
                 * `json` est un raccourci pour : new JsonResponse(...)
                 * Les entêtes HTTP minimaux sont pris en charge par la méthode
                 */
                return $this->json(self::AUTHORS[$id]);
            default:
                /*
                 * Sinon, nous retournons la vue HTML standard
                 */
                return $this->show($id);
        }
    }

    /**
     * Une requête secondaire qui affiche des informations contextuelles.
     * Ici, par exemple, les auteurs de même langue que celui affiché.
     * Ce contrôleur n'est pas associé à une route, car il n'est peut-être pas destiné à être appelé isolément
     *
     * @param string $lang
     * @return string
     */
    public function sameLanguageAuthors (string $lang)
    {
        $authors = array_filter(self::AUTHORS, function ($author) use ($lang) { return $lang == $author['lang']; });

        return $this->render('default/fragments/contextual_fragment.html.twig', ['authors' => $authors]);
    }

    /**
     * Variation sur l'exemple précédent, illustrant l'utilisation de l'objet de type Request produit par le routeur.
     * Nous voyons aussi un exemple d'injection de dépendance : les méthodes des contrôleurs admettent des paramètres
     *   arbitraires, en nombre et en type.
     *
     * @param Request $request
     * @return Response
     *
     * @Template("default/index.html.twig")
     * @Route("/authors/filter", name="authors_list_filtered")
     */
    public function authorsByLanguage(Request $request) : array
    {
        /*
         * Nous voulons récupérer un paramètre correspondant à la langue.
         * Contrairement aux cas précédents, cettevaleur n'est pas intégrée dans l'URL, mais dans la « chaîne de requête »
         * On utilisera cela pour les paramètres optionnels ou annexes du point de vue de la structure de la route.
         * Ces valeurs n'étant pas dans l'URL, nous devons accéder à l'objet Request (construit par le routeur) pour les lire.
         * L'URL aurait donc pour forme :
         * http://<chemin>/public/index.php/authors/filter?lang=fr
         *
         * Nous utilisons ici la coalescence de l'opérateur ternaire pour simplifier l'écriture.
         */
        $lang = $request->query->get('lang') ?? 'fr';
        $authors = array_filter(self::AUTHORS, function ($author) use ($lang) { return $lang == $author['lang']; });

        return [
            'authors' => $authors,
        ];
    }

    /**
     * Affiche la liste des auteurs, représentée comme une constante de configuration
     *
     * @Template("default/index.html.twig")
     * @Route("/authors/external", name="authors_external")
     */
    public function importedAuthors(): array
    {
        return [
            /*
             * Plutôt que de stocker notre catalogue sous forme de tableau PHP, nous pouvons le déporter
             * dans un fichier de configuration, à l'intérieur du dossier `config`.
             * En l'occurrence, un fichier ad hoc a été créé : /config/packages/dev/authors.yaml
             * (dans l'environnement `dev` car nous n'en n'aurions plus besoin en cas de déploiement de l'application)
             * Les constantes doivent être déclarées dans une section `parameters`
             *
             * Tous les contrôleurs peuvent lire les constantes de configuration grâce à la méthode `getParameter`
             */
            'authors' => $this->getParameter('authors'),
        ];
    }
}

