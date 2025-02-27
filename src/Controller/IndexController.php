<?php
namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;

class IndexController extends AbstractController
{
    private $entityManager;

    // Inject Doctrine's EntityManagerInterface via the constructor
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'article_list')]
    public function home(ManagerRegistry $doctrine): Response
    {
        // Retrieve all articles from the 'article' table in the DB
        $articles = $doctrine->getRepository(Article::class)->findAll();

        // Render the view with the retrieved articles
        return $this->render('articles/index.html.twig', ['articles' => $articles]);
    }

    #[Route('/article/new', name: 'new_article', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        // Create a new Article object
        $article = new Article();

        // Create a form for the article
        $form = $this->createFormBuilder($article)
            ->add('nom', TextType::class)
            ->add('prix', TextType::class)
            ->add('save', SubmitType::class, [
                'label' => 'Créer'
            ])
            ->getForm();

        // Handle the form submission
        $form->handleRequest($request);

        // Check if the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            // Save the article to the database
            $article = $form->getData();
            $this->entityManager->persist($article);
            $this->entityManager->flush();

            // Redirect to the article list page after saving
            return $this->redirectToRoute('article_list');
        }

        // Render the form in the template
        return $this->render('articles/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/article/save', name: 'article_save')]
    public function save(): Response
    {
        // Create a new article and set properties
        $article = new Article();
        $article->setNom('Article 3');
        $article->setPrix('3000'); // Ensure the price is a string

        // Use the injected entity manager
        $this->entityManager->persist($article);
        $this->entityManager->flush();

        return new Response('Article enregistré avec id ' . $article->getId());
    }

    /**
     * @Route("/article/{id}", name="article_show")
     */
    public function show($id, ManagerRegistry $doctrine): Response
    {
        // Use the Doctrine ManagerRegistry to get the repository and find the article
        $article = $doctrine->getRepository(Article::class)->find($id);

        // Check if the article was found
        if (!$article) {
            throw $this->createNotFoundException('The article does not exist.');
        }

        // Render the article details
        return $this->render('articles/show.html.twig', [
            'article' => $article
        ]);
    }

     /**
     * @Route("/article/edit/{id}", name="edit_article", methods={"GET", "POST"})
     */
    public function edit(ManagerRegistry $doctrine, Request $request, $id): Response
    {
        // Utilisation de ManagerRegistry pour récupérer l'entité
        $article = $doctrine->getRepository(Article::class)->find($id);

        // Vérifier si l'article existe
        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        // Création du formulaire
        $form = $this->createFormBuilder($article)
            ->add('nom', TextType::class)
            ->add('prix', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Modifier'])
            ->getForm();

        // Gestion de la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->flush(); // Sauvegarde les modifications

            return $this->redirectToRoute('article_list'); // Redirection après modification
        }

        return $this->render('articles/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

  /**
     * @Route("/article/delete/{id}", name="delete_article", methods={"GET", "POST"})
     */
    public function delete(Request $request, $id)
    {
        // Retrieve the article by ID
        $article = $this->entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            $this->addFlash('error', 'Article not found');
            return $this->redirectToRoute('article_list');
        }

        // If it's a POST request, delete the article
        if ($request->isMethod('POST')) {
            try {
                $this->entityManager->remove($article);
                $this->entityManager->flush();

                // Add success message and redirect to the article list after deletion
                $this->addFlash('success', 'Article deleted successfully!');
                return $this->redirectToRoute('article_list');
            } catch (\Exception $e) {
                // Handle potential errors during deletion
                $this->addFlash('error', 'Error deleting the article: ' . $e->getMessage());
                return $this->redirectToRoute('article_list');
            }
        }

        // If it's a GET request, handle the confirmation in the controller
        // Instead of rendering a template, directly redirect to the article list
        return $this->redirectToRoute('article_list');
    }


}
