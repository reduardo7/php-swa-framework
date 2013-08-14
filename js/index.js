$(function() {
	$('.icons_nav').flexslider({
		animation : "slide",
		directionNav : false,
		animationLoop : false,
		controlNav : false,
		slideshow : false,
		animationDuration : 300
	});
	$('.panels_slider').flexslider({
		animation : "slide",
		directionNav : false,
		controlNav : true,
		animationLoop : false,
		slideToStart : 1,
		animationDuration : 300,
		slideshow : false
	});
});

if ((navigator.userAgent.match(/iPhone/i))
		|| (navigator.userAgent.match(/iPod/i))) {
	$(window).load(function() {
		$("body").removeClass("home");
		$("body").addClass("homeiphone");
	});
}