<?php
error_reporting(0);
header('Content-Type: application/json');

/*
	Simple API Dunia21.me Scrapper
	By Viloid ~ Sec7or Team

	How to Use :
		api.php?p={pageNumber} 
		api.php?s={searchQuery}
		api.php?v={stringBase64}
		api.php?dl={stringBase64}

	Response : JSON
*/

if(isset($_GET['p'])){
	echo json_encode(getPage($_GET['p']));
}
if(isset($_GET['s'])){
	echo json_encode(search($_GET['s']));
}
if(isset($_GET['v'])){
	echo json_encode(getMovie(base64_decode($_GET['v'])));
}
if(isset($_GET['dl'])){
	echo json_encode(download(base64_decode($_GET['dl'])));
}

function getPage($n){
	if($n == "1"){
		$f = req("https://dunia21.me/latest/");
	}else
		$f = req("https://dunia21.me/latest/page/".$n."/");
		preg_match_all('/quality quality-(.*?)">/', $f, $q);
		preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/', $f, $m);
		unset($m[1][0]);
		$i = 0;
		foreach ($m[1] as $k) {
			preg_match('/"name": "(.*?)"/', $k, $title);
			preg_match('/"url": "https:\/\/dunia21.me\/(.*?)\/"/', $k, $movie);
			preg_match('/"image": "\/\/(.*?)"/', $k, $image);
			preg_match('/"genre": \[(.*?)\],/', $k, $g);
			$genre = explode(",", str_replace('"', "", preg_replace('/\s+/', '', $g))[1]);
			preg_match('/"ratingValue": "(.*?)"/', $k, $rating);
			preg_match('/"urlTemplate": "(.*?)"/', $k, $trailer);
			$data[] = array(
						"title" => $title[1],
						"url" => $_SERVER[HTTP_HOST].$_SERVER['PHP_SELF']."?v=".base64_encode($movie[1]),
						"image" => $image[1],
						"trailer" => $trailer[1],
						"genre" => $genre,
						"quality" => $q[1][$i],
						"rating" => $rating[1],
						"download" => $_SERVER[HTTP_HOST].$_SERVER['PHP_SELF']."?dl=".base64_encode($movie[1])
					);
			$i++;
		}
	return $data;
}

function search($n){
	$f = req("https://dunia21.me/?s=".$n);		
	preg_match_all('/<figure>(.*?)<\/script>/', $f, $m);
	$i = 0;
	foreach ($m[1] as $k) {
		preg_match('/title="(.*?)"/', $k, $title);
		preg_match('/<a href="https:\/\/dunia21.me\/(.*?)\/"/', $k, $movie);
		preg_match('/"image": "\/\/(.*?)"/', $k, $image);
		preg_match('/kualitas film (.*?)">/', $k, $q);
		preg_match('/"genre": \[(.*?)\],/', $k, $g);
		$genre = explode(",", str_replace('"', "", preg_replace('/\s+/', '', $g))[1]);
		preg_match('/"ratingValue": "(.*?)"/', $k, $rating);
		preg_match('/"urlTemplate": "(.*?)"/', $k, $trailer);
		$data[] = array(
					"title" => $title[1],
					"url" => $_SERVER[HTTP_HOST].$_SERVER['PHP_SELF']."?v=".base64_encode($movie[1]),
					"image" => $image[1],
					"trailer" => $trailer[1],
					"genre" => $genre,
					"quality" => $q[1],
					"rating" => $rating[1],
					"download" => $_SERVER[HTTP_HOST].$_SERVER['PHP_SELF']."?dl=".base64_encode($movie[1])
				);
			$i++;
		}
	return $data;
}

function download($n){
	$f = preg_replace('/\s+/', '', req('https://asdahsdkjajslkfbkaujsgfbjaeghfyjj76e8637e68723rhbfajkl.akurat.co/verifying.php?slug='.$n, '{slug:"'.$n.'"}'));	
	preg_match_all('/<tr>(.*?)<\/tr>/', $f, $r);
	unset($r[1][0]);
	unset($r[1][1]);		
	foreach ($r[1] as $k) {
		preg_match('/<strong>(.*?)<\/strong>/', $k, $p);
		preg_match_all('/<atarget="_blank"href="(.*?)"class="btnxbtn-(.*?)"/', $k, $d);
		$res[$p[1]] = array_combine($d[2], $d[1]);
	}
	return $res;
}

function stream($n){
	$f = req("https://dunia21.me/ajax/movie.php", "slug=".$n);
	preg_match_all('/<a href="(.*?)" target="iframe" class="(.*?)">/', $f, $d);
	return array_combine($d[2], $d[1]);
}

function getMovie($n){
	$f = req("https://dunia21.me/".$n."/");
	preg_match('/<figure><img src="\/\/(.*?)" alt="(.*?)"/', $f, $d);
	preg_match('/Synopsis<\/strong><br \/>(.*?)<br \/>/', $f, $s);
	$data = array(
				"title" => $d[2],
				"images" => $d[1],
				"synopsis" => $s[1],
				"stream" => stream($n),
				"download" => download($n)
			);
	return $data;
}

function req($url, $post=null){
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, $url);
	    if($post != null){
	    	curl_setopt($ch, CURLOPT_POST, true);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	    }
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$headers = array();
	$headers[] = 'User-Agent: Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.119 Mobile Safari/537.36';
	$headers[] = 'X-Requested-With: XMLHttpRequest';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	return curl_exec($ch);
}
