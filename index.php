<?php include('config.php'); include('functions.php'); ?><!DOCTYPE html>
<html lang="en">

<head>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">

	<title>SMPL Aggregator</title>

	<!-- Bootstrap Core CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet">

	<!-- Custom CSS -->
	<link href="css/modern-business.css" rel="stylesheet">

	<!-- More Custom CSS -->
	<link href="css/style.css" rel="stylesheet">
	
	<!-- More Custom CSS -->
	<link href="css/custom.css" rel="stylesheet">

	<!-- Custom Fonts -->
	<link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->

</head>

<body>

	<!-- Navigation -->
	<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
	<div class="container-fluid">
		<!-- Brand and toggle get grouped for better mobile display -->
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="">SMPL Aggregator</a>
		</div>

		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
			
			<form class="navbar-form navbar-right" method="post" role="search">
				<div class="form-group">
					<input type="text" class="form-control" placeholder="Tags">
				</div>
				<button type="submit" class="btn btn-default">Submit</button>
			</form>
			<ul class="nav navbar-nav navbar-right">
				<li>
					<a href="javascript:;">Login</a>
				</li>
				<li>
					<button type="button" class="btn btn-primary navbar-btn">Sign up</button>
				</li>
			</ul>
		</div><!-- /.navbar-collapse -->
	</div><!-- /.container-fluid -->
</nav>	

	<!-- Page Content -->
	<div class="container" id="content">
		<!-- Team Members -->
		<div class="row articles">


			<!--<div class="col-lg-12">
				<h2 class="page-header">Tags: </h2>
			</div>-->
<?php

$sql = '
	SELECT
		DISTINCT l.url,
		l.title AS title,
		l.id AS id,
		l.description,
		l.show_description,
		l.source_id,
		l.publish_date,
		l.featured,
		l.sticky,
		l.image,
		l.image_height,
		l.image_width,
		l.clicks,
		s.name AS source_name,
		s.source AS source_source,
		GROUP_CONCAT(DISTINCT st.tag_id) AS tag_ids,
		GROUP_CONCAT(DISTINCT t.name) AS tag_names,
		GROUP_CONCAT(DISTINCT t.slug) AS tag_slugs
	FROM
		links AS l
	INNER JOIN
		sources AS s
	ON
		s.id = l.source_id
	INNER JOIN
		source_tags AS st
	ON
		s.id = st.source_id
	INNER JOIN
		tags as t
	ON
		st.tag_id = t.id
	WHERE
		l.publish_date <= NOW()
	AND
		l.active = 1
	AND
		l.source_id IS NOT NULL
	AND
		l.image != ""
	GROUP BY
		l.id
	ORDER BY
		l.sticky
			DESC,
		l.sticky_rank
			ASC,
		l.publish_date
			DESC
	LIMIT
		12
';
$result = $conn->query($sql) OR die('Query error: '. mysqli_error($conn));

$j = 1;

if ($result->num_rows > 0) {
	while ($article = $result->fetch_assoc()) {
?>
			<div class="item">
				<div class="thumbnail">
					
					<?php if ($article['image'] != '') { ?>
						<a href="redirect.php?id=<?= $article['id']; ?>" target="_blank">
							<img class="img-responsive" src="<?= $article['image']; ?>">
						</a>
					<?php } ?>
					<?php if ($article['sticky'] > 0) { ?><i class="fa fa-2x fa-thumb-tack sticky thumbnail-overlay"></i><?php } ?>
					<?php if ($article['featured'] > 0) { ?><i class="fa fa-2x fa-star featured thumbnail-overlay<?= ($article['sticky']) ? (' thumbnail-overlay-secondary') : (''); ?>"></i><?php } ?>
						<h4 class="title">
							<a href="redirect.php?id=<?= $article['id']; ?>" target="_blank"><?= $article['title']; ?></a>
						</h4>
					<div class="text-center">
						<?= $article['source_source']; ?> - <?= date('n.d.y', strtotime($article['publish_date'])); ?>
					</div>
					<?php if ($article['description'] != '' && $article['show_description'] > 0) { ?><p class="caption"><?= substr($article['description'], 0, 140); ?>...</p><?php } ?>
					<?php
						if ($article['tag_names'] != '') {
							$tag_names = explode(',', $article['tag_names']);
							$tag_slugs = explode(',', $article['tag_slugs']);
							$tag_urls = array();
							for($i = 0; $i < count($tag_slugs); $i++) {
								$tag_urls[] = '<a href="?tag='. $tag_slugs[$i] .'">'. $tag_names[$i] .'</a>';
							}
							$tag_urls_str = implode(', ', $tag_urls);
					?>
						<p class="text-center"><?= $tag_urls_str ?></p>
					<?php } ?>
					
					<div class="text-center">
						<div class="btn-group btn-group-justified" role="group">
							<a class="btn btn-default"><?= humanizeNum($article['clicks']); ?></a>
							<a class="btn btn-default"><i class="fa fa-facebook fb"></i></a>
							<a class="btn btn-default"><i class="fa fa-twitter tw"></i></a>
							<a class="btn btn-default bookmark bookmark-id-<?= $article['id']; ?>"><i class="fa fa-heart-o heart"></i></a>
						</div>
					</div>
				</div>
			</div>
<?php
if ($j % 4 == 0) {
	//print '</div><div class="row">';	
}
$j++;
?>
<?php } ?>
<?php }  else { print 'no results'; } ?>
<?php /*
			<div class="col-lg-3 col-md-4 col-sm-6 item">
				<div class="thumbnail">
					<a href="javascript:;">
						<img class="img-responsive" src="http://placehold.it/750x450" alt="">
					</a>
					<div class="text-center">
						<h4>
							<a href="javascript:;">John Smith</a>
						</h4>
						Job Title
					</div>
					<p class="caption">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Iste saepe et quisquam nesciunt maxime.</p>
					
					<div class="text-center">
						<div class="btn-group" role="group">
							<button type="button" class="btn btn-default"><i class="fa fa-facebook fb"></i></button>
							<button type="button" class="btn btn-default"><i class="fa fa-twitter tw"></i></button>
							<button type="button" class="btn btn-default bookmark"><i class="fa fa-heart-o heart"></i></button>
						</div>
					</div>
				</div>
			</div>
*/ ?>	
			
			
		</div>
		<!-- /.row -->
	</div>
	
	<div class="container">
		<div class="row text-center">
			<nav aria-label="Page navigation">
			  <ul class="pagination">
			    <li>
			      <a href="javascript:;" aria-label="Previous">
			        <span aria-hidden="true">&laquo;</span>
			      </a>
			    </li>
			    <li class="active"><a href="javascript:;">1</a></li>
			    <li><a href="javascript:;">2</a></li>
			    <li><a href="javascript:;">3</a></li>
			    <li><a href="javascript:;">4</a></li>
			    <li><a href="javascript:;">5</a></li>
			    <li><a href="javascript:;">7</a></li>
			    <li><a href="javascript:;">8</a></li>
			    <li>
			      <a href="javascript:;" aria-label="Next">
			        <span aria-hidden="true">&raquo;</span>
			      </a>
			    </li>
			  </ul>
			</nav>
		</div>
	</div>
	
	<div class="container">
		<!-- Our Customers -->
		<div class="row">
			<div class="col-lg-12">
				<h2 class="page-header">Our Sources</h2>
			</div>
			<div class="col-md-2 col-sm-4 col-xs-6">
				<img class="img-responsive customer-img vcenter" src="http://static.gamefront.com/cutoff/images/default/logo.svg" alt="">
			</div>
			<div class="col-md-2 col-sm-4 col-xs-6">
				<img class="img-responsive customer-img vcenter" src="images/ign-logo.png" alt="">
			</div>
			<div class="col-md-2 col-sm-4 col-xs-6">
				<img class="img-responsive customer-img vcenter" src="http://allforgamenews.com/wp-content/uploads/2013/07/myweblogo.png" alt="">
			</div>
			<div class="col-md-2 col-sm-4 col-xs-6">
				<img class="img-responsive customer-img vcenter" src="images/charlie-intel-logo.png" alt="">
			</div>
			<div class="col-md-2 col-sm-4 col-xs-6">
				<img class="img-responsive customer-img vcenter" src="http://aliensoverhaul.mattfiler.co.uk/related/shacknews.png" alt="">
			</div>
			<div class="col-md-2 col-sm-4 col-xs-6">
				<img class="img-responsive customer-img vcenter" src="https://s.blogsmithmedia.com/www.engadget.com/assets-h68a27a86bb75b3af3b2e868042d2aa99/images/eng-logo-928x201.png?h=f2ab80e02d55834504088500b44a23cf" alt="">
			</div>
		</div>
		<!-- /.row -->
	</div>

	<hr>

	<div class="container">
		<!-- Footer -->
		<footer>
			<div class="row">
				<div class="col-lg-12">
					<p>Copyright &copy; SMPL Aggregator <?= date('Y'); ?></p>
				</div>
			</div>
		</footer>

	</div>
	<!-- /.container -->

	<!-- jQuery -->
	<script src="js/jquery.js"></script>

	<!-- Bootstrap Core JavaScript -->
	<script src="js/bootstrap.min.js"></script>
	
	<!-- Custom JavaScript -->
	<script src="js/app.js"></script>
	

</body>

</html>
