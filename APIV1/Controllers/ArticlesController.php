<?php

class ArticlesController extends Controller {

    /* Метод, возвращающий все новости */
    public function allArticles($request = new Request) {

        $DB = new DB();

        /* Проверяем наличие параметра page в url */
        $page = isset($request->params["page"]) ? (int) $request->params["page"] : 1;

        /* Определяем сортировку */
        $defaultOrder = "created_at";
        if (isset($request->params["order_by"])) {
            $orderParams = ['author_name', 'rating'];
            foreach($orderParams as $order) {
                if ($order === $request->params["order_by"]) {
                    $defaultOrder = $order;
                    break;
                }
            }
        }

        /* Проверяем выборку по тегам */
        if (isset($request->params['tags'])) {
            $pathToFilter = "./APIV1/Filters/UsableFilters/ArticleFilter.php";
            if(file_exists($pathToFilter)) {
                include $pathToFilter;
                $filters = new ArticleFilter(isset($request->params) ? $request->params : []);
                return $this->json(
                    $DB->select('articles')
                        ->innerJoin('articles_tags', 'articles_tags.article_id', '=', 'articles.id')
                        ->where($filters->filterOr())
                        ->orderBy($defaultOrder)
                        ->get()
                );
            }
        }

        return $this->json($DB->select('articles')->orderBy($defaultOrder)->limit($page, 30)->get());
    }


    /* Метод, позволяющий создать новую новость */
    public function createArticle(Request $request) {

        /* Проверяем, что передан заголовок и тело статьи */
        if (isset($request->params['article_header']) && isset($request->params['article_body'])) {

        }

    }

}