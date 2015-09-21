$(document).ready(function(){

	$('.filter-action').click(function(){
		var nextMove = $(this).children('img').attr('alt');
		var contentSeeMore = '<img class="sprite-icon-dark-arrow-down" src="';
		contentSeeMore = contentSeeMore.concat(blank);
		contentSeeMore = contentSeeMore.concat('" alt="v" /> ');

		var contentSeeLess = '<img class="sprite-icon-dark-arrow-up" src="';
		contentSeeLess = contentSeeLess.concat(blank);
		contentSeeLess = contentSeeLess.concat('" alt="^" /> ');

		if (nextMove == '^')
		{
			$(this).parent().parent().children().each(function(index) {
				if (index > 3 && $(this).children('a').attr('class') != 'filter-action' && $(this).attr('class') != 'active')
					$(this).hide(300);
				else if ($(this).children('a').attr('class') == 'filter-action')
					$(this).children('a').html(contentSeeMore.concat(seeMore));
			});
		}
		else if (nextMove == 'v')
		{
			$(this).parent().parent().children().each(function(index) {
				if (index > 3 && $(this).children('a').attr('class') != 'filter-action' && $(this).attr('class') != 'active')
					$(this).show(300);
				else if ($(this).children('a').attr('class') == 'filter-action')
					$(this).children('a').html(contentSeeLess.concat(seeLess));
			});
		}
		return false;
	});

});
