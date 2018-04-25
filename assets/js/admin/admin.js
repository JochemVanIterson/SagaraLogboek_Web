$(document).ready(function() {
	$('.admin_selector_dd').click(function(){
		$('.admin_selector_itm').slideToggle("fast");
	});
	$('.admin_selector_itm').click(function(){
		var selected = $(this).text();
		
		if($('.admin_selector_dd').is(":visible")){
			$('.admin_selector_itm').slideUp("fast");
		}
		openPage(selected);
	});
	
	openPage(page);
});

function openPage(page){
	$('.admin_page').load(page+'.php?included');
	$('.admin_selector_dd').text(page);
	$('.admin_selector_itm').removeClass("Selected");
	$('#'+page+'.admin_selector_itm').addClass("Selected");
}