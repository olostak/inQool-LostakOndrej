<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;


final class ArticlePresenter extends Nette\Application\UI\Presenter
{
    private Nette\Database\Explorer $database;

    public function __construct(Nette\Database\Explorer $database)
    {
        $this->database = $database;
    }

    public function renderShow(int $articleId): void
    {
         $article = $this->database
            ->table('articles')
            ->get($articleId);
         if(!$article){
             $this->error("Page not found!", 404);
         }

        $this->template->article = $article;
        $this->template->categories = $article->related('categories')->order('category DESC');
    }
}