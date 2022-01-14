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
        $form->addText('title', 'Title:')
            ->setRequired();
        $form->addTextArea('content', 'Content:')
            ->setRequired();
        $form->addTextArea('categories', 'Categories:');

        $form->addSubmit('send', 'Save');
        $form->onSubmit[] = [$this, 'submit'];
        $form->onSuccess[] = [$this, 'createFormSucceeded'];

        return $form;
    }

    public function createFormSucceeded(array $data): void
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

        $this->flashMessage('Article submitted successfully', 'success');
        $this->redirect('Article:show', $article->id);
    }

    public function submit($form)
    {

        if ($this->isAjax()) {
            $this->redrawControl("createForm");
        }
    }
}
