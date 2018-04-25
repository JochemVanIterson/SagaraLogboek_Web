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
		var formData = new FormData(form[0]);
		console.log(formData);
		var id = form.attr('id');
		id = id.replace(/\./g,'\\.');
		if(id!="AddCategory" && confirm(buttonpressed)){
			$.ajax({
				type: "POST",
				url: BaseURL+"Scripts/Category.php?action="+buttonpressed,
				enctype: 'multipart/form-data',
				processData: false,  // Important!
				contentType: false,
				cache: false,
				data: formData, // serializes the form's elements.
				success: function(data){
					if(buttonpressed=="Remove"){
						console.log(data);
						$('#'+id+'.list_element').remove();
						$('.admin_page').load('Categories.php?included');
					} else {
						console.log(data);
						$('#'+id+'.list_element').find('.content').slideToggle("fast");
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
	$('.addFieldButton').click(function(event){
		event.preventDefault();
		row = $(this).closest('tr');
		$(fieldDefault(addFieldInc)).insertBefore(row);
		addFieldInc+=1;
		$('.removeFieldButton').click(fieldRemoveAction);
		table = row.parent();
		table.find('.emptyField').hide();
	});
	$('.removeFieldButton').click(fieldRemoveAction);
});

function createFirst(){
	$(".empty_view").hide();
	$("#AddCategoryDiv").find('.content').show();
	$("#AddCategoryDiv").show();
}

function addCategory(){
	form = $('.AddCategoryForm');
	var formData = new FormData(form[0]);
	if(confirm("Add")){
		$.ajax({
			type: "POST",
			url: BaseURL+"Scripts/Category.php?action=Add",
			enctype: 'multipart/form-data',
			processData: false,
			contentType: false,
			cache: false,
			data: formData, // serializes the form's elements.
			success: function(data){
				console.log(data);
				$('.admin_page').load('Categories.php?included');
			}
		});
	}
}

function fieldRemoveAction(event){
	event.preventDefault();
	row = $(this).closest('tr');
	table = row.parent();
	row.remove();
	if(table.children().length == 3){
		table.find('.emptyField').show();
	}
}

function objectifyForm(formArray) {
	var returnArray = {};
	for (var i = 0; i < formArray.length; i++){
		returnArray[formArray[i]['name']] = formArray[i]['value'];
	}
	return returnArray;
}

function fieldDefault(id){
id = "field"+id;
return "\
	<tr>\
		<td>\
			<input type='text' name='" + id + "_field'>\
		</td>\
		<td>\
			<select name='" + id + "_type'>\
				<option value='' disabled selected>Empty</option>\
				<option value='calculation'>Calculation</option>\
				<option value='smalltext'>Small Text</option>\
				<option value='largetext'>Large Text</option>\
				<option value='image'>Image</option>\
			</select>\
		</td>\
		<td>\
			<label class='switch'>\
				<input type='checkbox' name='" + id + "_required'>\
				<span class='slider round'></span>\
			</label>\
		</td>\
		<td>\
			<button class='removeFieldButton'>X</button>\
		</td>\
	</tr>";
}