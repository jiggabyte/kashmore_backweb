"use strict";

jQuery(document).on('ready', function() { 

	checkScrollAnimation();
	initSwiper();
	initEvents();
	initMap();
	initCollapseMenu();	

	jQuery('.matchHeight').matchHeight();
});

jQuery(window).on('scroll', function (event) {

	checkNavbar();
	checkScrollAnimation();
}).scroll();

jQuery(window).on('load', function(){

	initMasonry()
});

/* Collapse menu slide */
function initCollapseMenu() {

	var navbar = jQuery('#navbar'),
		navbar_toggle = jQuery('.navbar-toggle'),
		navbar_wrapper = jQuery("#nav-wrapper");

    navbar_wrapper.on('click', '.navbar-toggle', function (e) {

        navbar_toggle.toggleClass('collapsed');
        navbar.toggleClass('collapse');
        navbar_wrapper.toggleClass('mob-visible');
    });

    navbar_wrapper.on('click', '.menu-item-has-children > a', function() {

    	var el = jQuery(this);

    	el.next().toggleClass('show');
    	el.parent().toggleClass('show');
    	return false;
    });

    var lastWidth;
    jQuery(window).on("resize", function () {

    	checkNavbar();

    	var winWidth = jQuery(window).width();

        if (winWidth > 992 && navbar_toggle.is(':hidden')) {
            navbar.addClass('collapse');
            navbar_toggle.addClass('collapsed');
        }

       	lastWidth = winWidth;
    });	
}

/* Navbar is set darker on main page on scroll */
function checkNavbar() {

	var scroll = jQuery(window).scrollTop(),
    	navBar = jQuery('nav.navbar'),
    	topBar = jQuery('.top-bar'),
	    slideDiv = jQuery('.slider-full');

	if (topBar.length) {

		jQuery('.navbar-affix').affix({
		      offset: {
		        top: topBar.height(),
		      }
		});
	}

    if (scroll > 1) navBar.addClass('dark'); else navBar.removeClass('dark');
}

/* All keyboard and mouse events */
function initEvents() {

	jQuery('.swipebox').swipebox();

	jQuery('.menu-types').on('click', 'a', function() {

		var el = jQuery(this);

		el.addClass('active').siblings('.active').removeClass('active');
		el.parent().find('.type-value').val(el.html());

		return false;
	});

	/* Scrolling to navbar from "go top" button in footer */
    jQuery('footer').on('click', '.go-top', function() {

	    jQuery('html, body').animate({ scrollTop: 0 }, 800);
	});

    jQuery('.alert').on('click', function() {

	    jQuery(this).parent().fadeOut();
	    return false;
	});	

	jQuery('form:not(.checkout) select:not(#rating)').wrap('<div class="select-wrap"></div>');

	jQuery('.woocommerce div.quantity,.woocommerce-page div.quantity').append('<span class="more"></span><span class="less"></span>');

	//Change quantity add to cart button
	jQuery(document).on('updated_wc_div', function () {

		jQuery('.woocommerce div.quantity,.woocommerce-page div.quantity').append('<span class="more"></span><span class="less"></span>');
	});


	jQuery('.woocommerce').on('click', 'div.quantity > span', function(e) {

		var f = jQuery(this).siblings('input');
		if (jQuery(this).hasClass('more')) {
			f.val(Math.max(0, parseInt(f.val()))+1);
		} else {
			f.val(Math.max(1, Math.max(0, parseInt(f.val()))-1));
		}
		e.preventDefault();

		jQuery(this).siblings('input').change();

		return false;
	});

}

/* Swiper slider initialization */
function initSwiper() {

	var clientsSwiperEl = jQuery('.testimonials-slider'),
		gallerySwiperEl = jQuery('.swiper-gallery'),
		textSwiperEl = jQuery('.swiper-text');

	/* Slider for clients on main page */	
	if (clientsSwiperEl.length) {

	    var clientsSwiper = new Swiper(clientsSwiperEl, {
			direction   : 'horizontal',

			speed		: 1000,
			nextButton	: '.arrow-right',
			prevButton	: '.arrow-left',
			slidesPerView : 3,
		
			autoplay    : 7000,
			autoplayDisableOnInteraction	: false,
	    });
	}

	/* Slider for inner pages */	
	if (gallerySwiperEl.length) {	

	    var gallerySwiperEl = new Swiper(gallerySwiperEl, {
			direction   : 'horizontal',
	        pagination: '.swiper-pagination',
	        paginationClickable: true,		
			autoplay    : 4000,
			autoplayDisableOnInteraction	: false,        
	    });
	}

	if (textSwiperEl.length) {	

	    var textSwiperEl = new Swiper(textSwiperEl, {
			direction   : 'horizontal',
			nextButton	: '.arrow-right',
			prevButton	: '.arrow-left',
			loop		: true,
			autoplay    : 4000,
			autoplayDisableOnInteraction	: false,        
	    });
	}	

	jQuery(window).on('resize', function(){

		var ww = jQuery(window).width()
		if (clientsSwiperEl.length) {

			if (ww > 1000) { clientsSwiper.params.slidesPerView = 3; }
			if (ww <= 1000) { clientsSwiper.params.slidesPerView = 2; }
			if (ww <= 479) { clientsSwiper.params.slidesPerView = 1; }		
		
			clientsSwiper.update();			
		}
	}).resize();
}

/* Masonry initialization */
function initMasonry() {

	jQuery('.masonry').masonry({
	  itemSelector: '.item',
	  columnWidth:  '.item'
	});		
}

/* Scroll animation used for homepages */
function checkScrollAnimation() {

	var scrollBlock = jQuery('.car-right');
    if (scrollBlock.length) {

	    var scrollTop = scrollBlock.offset().top - window.innerHeight;

	    if (!scrollBlock.data('done') && jQuery(window).scrollTop() > scrollTop) {

	    	jQuery('.car-right  img').addClass('slideleft');
	    	scrollBlock.data('done', 1);
	    }  
	}
}

/* Google maps init */
function initMap() {

	jQuery('.like-google-maps').each(function(i, mapEl) {

		mapEl = jQuery(mapEl);
		if (mapEl.length) {

			var uluru = {lat: mapEl.data('lat'), lng: mapEl.data('lng')};
			var map = new google.maps.Map(document.getElementById(mapEl.attr('id')), {
			  zoom: mapEl.data('zoom'),
			  center: uluru,
			  scrollwheel: false,
			  styles: mapStyles
			});

			var marker = new google.maps.Marker({
			  position: uluru,
			  icon: taxipark_scripts.base_href + '/assets/images/location-black.png',
			  map: map
			});
		}
	});
}

