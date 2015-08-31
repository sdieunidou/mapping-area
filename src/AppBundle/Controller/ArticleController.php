<?php

namespace AppBundle\Controller;

use AppBundle\Form\ArticleTitleType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ArticleController
 */
class ArticleController extends Controller
{
    /**
     * @Route(pattern="/{slug}", name="engine_list")
     *
     * @param $slug
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction($slug)
    {
        $engine = $this->get('app.engine_manager')->getOneBySlug($slug);
        if (!$engine) {
            throw $this->createNotFoundException('Engine not found');
        }

        $list = $this->get('app.article_manager')->getByEngine($engine);

        return $this->render('AppBundle:Article:list.html.twig', [
            'engine' => $engine,
            'list'   => $list,
        ]);
    }

    /**
     * @Route(pattern="/{engineSlug}/{slug}", name="article_show")
     *
     * @param $slug
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction($slug)
    {
        $article = $this->get('app.article_manager')->getOneBySlug($slug);
        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        return $this->render('AppBundle:Article:show.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @Route(path="/admin/articles/{id}/edit/title", methods={"POST"}, name="article_edit_title")
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function editTitleAction(Request $request, $id)
    {
        $article = $this->get('app.article_manager')->getOneById($id);
        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        $value = $request->request->get('value');
        if (!empty($value)) {
            $article->setTitle($request->request->get('value'));
            $this->get('doctrine.orm.entity_manager')->flush();
        }

        return new Response();
    }
}

