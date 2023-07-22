<?php

class ArticleFilter extends BaseFilter {

    public function tags(array $tags) {
        foreach($tags as $tag) {
            array_push($this->whereOrConditions, ['articles_tags.tag_name', '=', $tag]);
        }
    }

}