
var $ = jQuery;
if ( window.history.replaceState ) {
  window.history.replaceState( null, null, window.location.href );
}


/**
 * Homepage animations
 * Features section
 */
$(window).scroll(function (){
  let featureItemsRow = $(".features .items");
  let featuresPosition = $(featureItemsRow).offset().top;
  if( window.scrollY >= (featuresPosition - 700) ){
    featureItemsRow.addClass("animated")
  }
});


$(document).ready(function (){

  /**
   * Homepage WhyUs section carousel for mobile
   */
  if ($('.homeWhyUsCarousel').length) {
    $('.homeWhyUsCarousel').owlCarousel({
      loop: true,
      margin: 10,
      autoplay: true,
      autoplayTimeout: 3000,
      autoplayHoverPause: true,
      nav: true,
      dots: false,
      items: 1,
      navText: ['<img src="'+bozy_options.theme_url+'/assets/images/left-arrow.png">', '<img class="rotate180" src="'+bozy_options.theme_url+'/assets/images/left-arrow.png">']
    });
  }


  // mobile menu action
  $(".menuBtn , .menuClose").click(function (){
    $(".mobileNav").toggleClass("open");
  });

  // footer nav toggle open/close
  $(".footerNavToggle").click(function (){
    let id = $(this).attr("data-nav");
    $(".nav[data-nav="+id+"]").toggleClass("open");
  });


  // homepage seo description box toggle action
  $('.seo-section .showMore').click(function (){
    let _this = $(this);
    let box = _this.parents('.seo-section').find('.textBox');
    box.toggleClass("open");
    if( box.hasClass("open") ){
      _this.html(`
      Show Less
      <svg class="ms-2" data-nav="2"  xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
      <path d="M4 6L7.29289 9.29289C7.62623 9.62623 7.79289 9.79289 8 9.79289C8.20711 9.79289 8.37377 9.62623 8.70711 9.29289L12 6" stroke="#2EA8E6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      `);
    }
    else {
      _this.html(`
      Show More
      <svg class="ms-2" data-nav="2"  xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
      <path d="M4 6L7.29289 9.29289C7.62623 9.62623 7.79289 9.79289 8 9.79289C8.20711 9.79289 8.37377 9.62623 8.70711 9.29289L12 6" stroke="#2EA8E6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      `);
    }
  })

});


