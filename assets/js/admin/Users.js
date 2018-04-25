var buttonpressed = "";
$(document).ready(function() {
	$('.list_element > .header').click(function(){
		$(this).closest('.list_element').find('.content').slideToggle("fast");
	});
	
	$(window).keydown(function(event){
		if(event.keyCode == 13) {
			event.preventDefault();
			return false;
		}
	});
	$('form').submit(function(event) {
		// Stop the browser from submitting the form.
		event.preventDefault();
		form = $(this);
		var formData = form.serialize();
		var id = form.attr('id');
		id = id.replace(/\./g,'\\.');
		if(id!="AddUser" && confirm(buttonpressed)){
			$.ajax({
				type: "POST",
				url: BaseURL+"Scripts/User.php?action="+buttonpressed,
				data: formData, // serializes the form's elements.
				success: function(data){
					if(buttonpressed=="Remove"){
						$('#'+id+'.list_element').remove();
					} else {
						$('#'+id+'.list_element').find('.content').slideToggle("fast");
						var data = objectifyForm(form.serializeArray());
						$('#'+id+'.list_element').find('.header').text(data.firstname + " " + data.lastname);
						console.log(data);
						if(data.admin == "on"){
							$('#'+id+'.list_element').find('.header').addClass("admin");
						} else {
							$('#'+id+'.list_element').find('.header').removeClass("admin");
						}
					}
				}
			});
		}
	});
	
	$('.saveButton').click(function(){
		buttonpressed = "Update";
	});
	$('.removeButton').click(function(){
		buttonpressed = "Remove";
	});
});

function addUser(){
	form = $('.AddUserForm');
	var formData = form.serialize();
	if(confirm("Add")){
		$.ajax({
			type: "POST",
			url: BaseURL+"Scripts/User.php?action=Add",
			data: formData, // serializes the form's elements.
			success: function(data){
				$('.admin_page').load('Users.php?included');
			}
		});
	}
}

function objectifyForm(formArray) {
	var returnArray = {};
	for (var i = 0; i < formArray.length; i++){
		returnArray[formArray[i]['name']] = formArray[i]['value'];
	}
	return returnArray;
}