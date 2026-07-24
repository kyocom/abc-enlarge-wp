(function($){
	var document = window.document,
	style = document.createElement('style');
	style.type = 'text/css';
	style.media = 'screen';
	rule = document.createTextNode('a .abc-enlarge{cursor:zoom-in} a ._abc_enlarged{cursor:zoom-out;} figure:has(.abc-enlarge){width: fit-content;} figure:has(.abc-enlarge.alignleft){float: left;} figure:has(.abc-enlarge.alignright){float: right;} .abc-enlarge,._abc_enlarged{transition:all .4s ease-in-out !important;-webkit-transition:all .4s ease-in-out !important}._abc_f{position:relative;display:block;overflow:auto;width:100vw;height:100vh;}._abc_i{float:none;max-width:400% !important}');
	if(style.styleSheet){
		style.styleSheet.cssText = rule.nodeValue;
	}else{
		style.appendChild(rule);
	};
	$('head').append(style);

	$('.gallery-columns-0 img').addClass('abc-enlarge');
})(jQuery);

(function($){
	$('img.abc-enlarge').click(function(){
		var isTouchDevice = navigator.userAgent.match(/(iPhone|iPod|iPad|Android|webOS|BlackBerry|IEMobile|Opera Mini|BB10|Windows Phone|Tizen|Bada)/);
		var obj = $(this);
		var w = obj.attr('width'),
			h = obj.attr('height'),
			marginLeft = obj.parent().parent().offset().left;

		if(obj.hasClass('_abc_enlarged')){
			// store small image
			var smallersrc = obj.attr('smallsrc'),
				smallersrcset = obj.attr('smallsrcset');

			if(isTouchDevice){
				obj.css({ width: 'auto', height: h });
				obj.parent().css('left', 0);// reset left position
				obj.parent().removeClass('_abc_f');
				obj.removeClass('_abc_i');
			}else{
				obj.css({ width: w, height: 'auto' });
			}
			
			obj.removeClass('_abc_enlarged');
			// restore smaller images
			obj.attr('srcset', smallersrcset);
			obj.attr('src', smallersrc);
		}else{
			var clientHeight = document.documentElement.clientHeight,
				clientWidth = document.documentElement.clientWidth;
				
			if((isTouchDevice)&&(clientHeight > clientWidth)){
				var picWidth,
					picHeight,
					pos = obj.offset().top;
				obj.parent().addClass('_abc_f');
				obj.addClass('_abc_i');

				// if PhotoSwipe available,
				picWidth = obj.parent().attr('data-lbwps-width');
				picHeight = obj.parent().attr('data-lbwps-height');
				if(!picWidth){
					picWidth = (clientHeight * w / h);
					picHeight = 'auto';
				}
				$('html,body').animate({ scrollTop: pos });

				obj.parent().css({ width: clientWidth + 'px', height: clientHeight + 'px' }).css('left', (-1 * marginLeft) + 'px' );
				// Always write a valid height so a stale inline height left by a
				// previous restore (height: h) is overridden on re-enlarge.
				obj.css({ width: (picWidth + 'px'), height: (picHeight === 'auto' ? 'auto' : picHeight + 'px') });
				setTimeout(() => {
					obj.parent().animate({ scrollLeft:((picWidth - clientWidth) * 0.5) });
				}, 500)
			}else{
				obj.css({ width: '100%', height: 'auto' });
			}
			var larger = obj.parent('a').attr('href'),
				smallsrc = obj.attr('src'),
				smallsrcset = obj.attr('srcset');
			// store small image
			obj.attr('smallsrc', smallsrc);
			obj.attr('smallsrcset', smallsrcset);

			obj.attr('src', larger);
			obj.attr('srcset', larger);
			obj.addClass('_abc_enlarged');
		}
		return false;
	});

})(jQuery);
