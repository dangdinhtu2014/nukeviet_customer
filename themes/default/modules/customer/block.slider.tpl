<!-- BEGIN: main -->
<link rel="stylesheet" type="text/css" href="{NV_BASE_SITEURL}themes/{TEMPLATE}/images/{MOD_FILE}/flexslider/flexslider.css"/>
<script type="text/javascript" src="{NV_BASE_SITEURL}themes/{TEMPLATE}/images/{MOD_FILE}/flexslider/jquery.flexslider-min.js"></script>
<div id="flexslider-{RANDOM}">
	<div class="flexslider carousel">
		<ul class="slides">
			<!-- BEGIN: loop -->
			<li class="item" >
				<a href="{DATA.links}" title="{DATA.title}" > <img src="{DATA.thumb}" alt="{DATA.title}" /></a>
			</li>
			<!-- END: loop -->
		</ul>
		<script type="text/javascript">
			//<![CDATA[
			$('#flexslider-{RANDOM} .flexslider').flexslider({
				slideshow: false,
				itemWidth: 120,
				itemMargin: 5,
				minItems: 2,
				maxItems: 6,
				slideshowSpeed: 4000,
				animationSpeed: 600,
				controlNav: false,
				directionNav: true,
				reverse: false,
				animation: "slide"
			});
			//]]>
		</script>
	</div>
</div>
<!-- END: main --> 