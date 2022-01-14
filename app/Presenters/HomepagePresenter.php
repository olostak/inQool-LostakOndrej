<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


final class HomepagePresenter extends Nette\Application\UI\Presenter
{
    private Nette\Database\Explorer $database;

    public function __construct(Nette\Database\Explorer $database)
    {
        $this->database = $database;
    }

    protected function createComponentArticleForm(): Form
    {
        $form = new Form;
        $form->getElementPrototype()->class('ajax');
        $form->addText('title', 'Title:')
            ->setHtmlAttribute('placeholder', 'Article name')
            ->setHtmlAttribute('style', "margin: 10px 0; width: 250px;")
            ->setRequired();
        $form->addTextArea('content', 'Content:')
            ->setHtmlAttribute('placeholder', 'Content')
            ->setHtmlAttribute('style', "margin: 10px 0; width: 250px; height: 30%;")
            ->setRequired();
        $form->addTextArea('categories', 'Categories:')
            ->setHtmlAttribute('style', "margin: 10px 0; width: 250px; height: 20%;")
            ->setHtmlAttribute('placeholder', 'Category1, Category2, Category3...');

        $form->addSubmit('send', 'Save')
            ->setHtmlAttribute('class', "btn btn-success");
        $form->onSuccess[] = [$this, 'createFormSucceeded'];

        return $form;
    }

    public function createFormSucceeded($form, array $data): void
    {
        $categoriesStr = "";
        if (array_key_exists('categories', $data)) {
            $categoriesStr = $data['categories'];
            unset($data['categories']);
        }
        $article = $this->database
            ->table('articles')
            ->insert($data);
        if ($article) {
            $categoriesStr = preg_replace('/\s+/', '', $categoriesStr);
            $categories = explode(',', $categoriesStr);
            foreach ($categories as $category) {
                $insetValues = array('category' => $category, 'article_id' => $article->id);
                $this->database
                    ->table('categories')
                    ->insert($insetValues);
            }
        }

        if ($this->isAjax()) {
            $form->reset();
            $this->flashMessage('Article submitted successfully', 'success');
            $this->redrawControl("clearForm");
        }
        $this->redirect('Article:show', $article->id);
    }
}