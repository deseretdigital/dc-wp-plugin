<?php
class DeseretConnect_Client
{

    protected $wpdb = null;

    public function __construct($wpdb)
    {
        $this->wpdb = $wpdb;
    }

    public function getRequests($url, $api_key, $pending = true, $author_name = true, $post_type = 'post')
    {
        $ch = curl_init();

        $data = array(
            'method' => "getRequests",
            "id"     => "1",
            "params" => array(
                "key" => $api_key,
            ),
        );


        $data_json = json_encode($data);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        //$fp = fopen('/tmp/deseretconnect.json', 'w');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        //curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array ("Accept: application/json"));
        $result = curl_exec($ch);
        if(!$result){
            return;
        }

        curl_close($ch);
        $result = json_decode($result);

        foreach($result->result as $request){
            $pushNow = $request->pushNow;
            $head = $request->head;
            $contentIds = array();
            $video = null;

            // find a video before the docs so we can append it to the body of the doc
            if(count($request->videos) > 0) {
            	foreach($request->videos as $v) {
            		$video = $v;
            		break;
            	}
            }

            foreach($request->documents as $doc) {
                if(($contentId = $this->savePost($doc, $pending, $pushNow, $head, $author_name, $video, $post_type))) {
                    $contentIds[] = $contentId;
                    $video = null;
                }
            }
            // only save galleries if the doc was saved
            if($request->galleries && count($contentIds) > 0){
                foreach($request->galleries as $gallery){
                    $this->saveGallery($gallery, $contentIds);
                }
            }
        }
    }

    /**
     * Saves a post from DeseretConnect
     * @param  Object $document
     */
    public function savePost($document, $pending, $pushNow = false, $head = null, $author_name = true, $video = null, $post_type = 'post')
    {

        // default to the first/last - or use the byline if we have it.
        $authorName = $document->authors[0]->firstName . " " . $document->authors[0]->lastName;
        if(!empty($document->authors[0]->byline)) {
            $authorName = $document->authors[0]->byline;
        }

        $metaPrefix = '_dc_';
        $metaFields = array(
            'content_id'   => $document->contentId,
            'request_id'   => $document->requestId,
            'beacon'       => $document->beacon,
            'author'       => $authorName,
            'author_email' => $document->authors[0]->publicEmail,
        );

        $postData = array();
        $postData['post_category'] = array($this->getCategory($document->category));
        $postData['post_content'] = $this->getContentBody($document); //The full text of the post.
        $postData['post_date'] = current_time('mysql'); // The time post was made. current_time uses the WP timezone
        $postData['post_date_gmt'] = gmdate('Y-m-d H:i:s'); //The time post was made, in GMT. (maybe?)
        $postData['post_excerpt'] = $document->description;

        // append the video to the end of the post. WP 3.5+ "should" turn the url into an embed.
        if(is_object($video) && !empty($video->url)) {
            $postData['post_content'] .= "\n". $video->url;
            if(!empty($video->description)) {
                $postData['post_content'] .= "\n". $video->description;
            }
        }

        if($author_name){
            $postData['post_content'] = '<div class="author">'.$metaFields['author'].'</div>'.$postData['post_content'];
        }
        if($pending == true) {
            $postData['post_status'] = 'pending';
        } else {
            $postData['post_status'] = 'publish';
        }
        $postData['post_title'] = $document->title;
        $postData['post_type'] = $post_type;
        $postData['tags_input'] = $this->getTags($document->keywords);
        //if we have an existing post, set the ID and update updated time
        if (($existingId = $this->getExistingPostId('_dc_content_id', $document->contentId)) !== false) {
            $postData['ID'] = $existingId;
            if(!$pushNow){
                return;
            }
        }

        $postId = wp_insert_post($postData, $error);
        if ($postId == 0) {
            return;
        }
        if($head){
            if($head->canonical){
                $metaFields['syn_canonical'] = $head->canonical;
            }
            if($head->standout){
                $metaFields['syn_standout'] = $head->standout;
            }
            if($head->syndication_source){
                $metaFields['syn_sydication_source'] = $head->syndication_source;
            }
            if($head->description){
                $metaFields['syn_description'] = $head->description;
            }
            if($head->authors){
                $authorText = '';
                foreach($head->authors as $author){
                    $authorText .= $author . "\n";
                }
                $metaFields['syn_authors'] = $authorText;
            }

        }
        $unique = true;
        foreach($metaFields as $key => $value){
            add_post_meta($postId, $metaPrefix . $key, $value, true);
        }

        return $postId;
    }

    public function getCategory($category)
    {
        $categoryId = '';
        $term = get_term_by('name', $category, 'category');
        if($term){
            $categoryId = $term->term_id;
        }
        return $categoryId;
    }

    public function getTags($keywords)
    {
        return $keywords;
    }

    public function getExistingPostId($field, $value)
    {
        $postId = false;
        $res = $this->wpdb->get_col(
          $this->wpdb->prepare(
            "SELECT post_id FROM {$this->wpdb->postmeta}
             WHERE meta_key = '%s'
             AND meta_value = %d
            "
            ,$field,$value )
        );
        if(count($res)){
            $postId = $res[0];
        }
        return $postId;
    }

    public function getContentBody($document)
    {
        $body = $document->body;
        if(isset($document->endNote) && $document->endNote){
            $body .= "\n<i>".$document->endNote."</i>";
        }
        if(isset($document->links) && $document->links){
            $body .= "\n".$document->links."";
        }
        if(isset($document->beacon) && $document->beacon){
            $body .= "\n".$document->beacon;
        }
        $body = str_ireplace("\n", "<br /><br />",$body);
        return $body;
    }

    public function saveGallery($gallery, $postIds)
    {
        foreach($postIds as $postId) {
            if($gallery->photos){
                foreach($gallery->photos as $photo) {
                    $parts = parse_url($photo->url);
                    $photoPath = preg_replace('/gallery\//', '', $parts['path']);
                    $wp_upload_dir = wp_upload_dir();
                    $filename = $wp_upload_dir['path'] . $photoPath;
                    if(!file_exists($filename)) {
                        $handle = fopen($filename, 'w');
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $photo->url);
                        curl_setopt($ch, CURLOPT_FILE, $handle);
                        $result = curl_exec($ch);
                        curl_close($ch);
                        fclose($handle);
                    }

                    $wp_filetype = wp_check_filetype(basename($filename), null );
                    $attachment = array(
                       'guid' => $wp_upload_dir['baseurl'] . _wp_relative_upload_path( $filename ),
                       'post_mime_type' => $wp_filetype['type'],
                       'post_title' => $photo->caption,
                       'post_excerpt' => $photo->caption . ' (' . $photo->credit . ')',
                       'post_content' => '',
                       'post_status' => 'inherit'
                    );
                    if (($existingId = $this->getExistingPostId('_dc_photo_id', $photo->id)) !== false) {
                        $attachment['ID'] = $existingId;
                    }
                    $attach_id = wp_insert_attachment( $attachment, $filename, $postId );
                    // you must first include the image.php file
                    // for the function wp_generate_attachment_metadata() to work
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
                    wp_update_attachment_metadata( $attach_id, $attach_data );

                    $metaPrefix = '_dc_';
                    $metaFields = array(
                        'photo_id'   => $photo->id,
                    );

                    $unique = true;
                    foreach($metaFields as $key => $value){
                        add_post_meta($attach_id, $metaPrefix . $key, $value, true);
                    }
                }
            }
        }
    }
}
