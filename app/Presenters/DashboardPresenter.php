<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

final class DashboardPresenter extends Nette\Application\UI\Presenter
{
    private Nette\Database\Explorer $database;

    public function __construct(Nette\Database\Explorer $database)
    {
        $this->database = $database;
    }

    public function renderDashboard(): void
    {
        $articlesJoinCategory = $this->database->query('
            SELECT articles.id, articles.title, articles.date, articles.content, categories.category
            FROM articles
            LEFT JOIN categories ON articles.id = categories.article_id ORDER BY articles.date DESC');
        $articles = array();
        $categories = array();
        foreach ($articlesJoinCategory->fetchAll() as $article) {
            $id = $article['id'];
            if (!array_key_exists($id, $articles)) {
                $articles[$id] = array();
                $articles[$id]['title'] = $article['title'];
                $articles[$id]['date'] = $article['date'];
                $articles[$id]['content'] = $article['content'];
                if ($article['category']) {
                    $articles[$id]['categories'] = array($article['category']);
                } else {
                    $articles[$id]['categories'] = array();
                }
            } else {
                $articles[$id]['categories'][] = $article['category'];
            }

            $category = $article['category'];
            if (!empty($category)) {
                if (!array_key_exists($category, $categories)) {
                    $categories[$category] = 1;
                } else {
                    $categories[$category] += 1;
                }
            }
        }
        arsort($categories);
        $this->template->articles = $articles;
        $this->template->categories = $categories;
    }
}