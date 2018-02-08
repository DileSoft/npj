<?php
  $this->message_set = array(

    "Subscribers.subscription_modes" => array(
          "facet_add"        => "новые записи по теме рубрики",
          "facet_diff"       => "изменения в документах по теме рубрики",
          "facet_post"       => "новые сообщения в ленте рубрики",
          "facet_comments"   => "новые комментарии в записях по теме рубрики",
          "record_diff"      => "изменения непосредственно в этом документе",
          "record_comments"  => "комментарии к этому документу",
          "cluster_add"      => "новые документы в кластере",
          "cluster_diff"     => "изменения в документах кластера",
          "cluster_post"     => "сообщения в журнале",
          "cluster_comments" => "комментарии в документах кластера",
          "comments_"        => "комментарии где-то в глубине дискуссии",
                                             ),
   
   );

  /*
     >class   | >object    | >methods
     facet    | record_id  | add/diff/post/comments/replica/commentreplica
     record   | record_id  | diff/comments/commentreplica  
     cluster  | record_id  | add/diff/post/comments/replica/commentreplica
     comments | comment_id | 
  */

?>