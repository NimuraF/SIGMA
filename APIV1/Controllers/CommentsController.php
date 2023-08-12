<?php

class CommentsController extends Controller {

    public function getCommentsForEntity(Request $request, string $category, string $entity_id) {

        $DB = new DB();

        if ( !$categories = $DB->select('categories_for_comments', ['category_name'])->get() ) {
            return Controller::errorMessage("Can't load categories!");
        }

        $find = false;

        foreach($categories as $category_n) {
            if ($category_n['category_name'] == $category) {
                $find = true;
                break;
            }
        }

        if (!$find) {
            return Controller::errorMessage('Incorrect category!');
        }

        $page = isset($request->params['page']) ? (int) $request->params['page'] : 1;

        if ( ($comments = $DB->select('comments')->where([['category', '=', $category], ['entity_id', '=', $entity_id]])->limit($page, 30)->get()) !== false ) {
            return $this->json($comments);
        }

        return Controller::errorMessage('Error while loading comments!');
    }

}