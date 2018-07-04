<?php
require_once ("../loader.php");

$post = new VTMPost();

$post->savePost([
	'post_title' => 'This is post title',
	'post_type' => 'post',
	'post_status' => 'publish',
	'feature_image' => "https://letweb.net/wp-content/uploads/2018/05/Layout336x280.png", // accept remote url & absolute url
    'post_content'  => "Content Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium atque consequuntur fugiat, id in incidunt ipsam ipsum itaque officiis omnis perspiciatis quam quasi quibusdam quisquam sapiente totam veniam vitae voluptatibus."
]);

echo $post->getId();