var buttonpressed = "";
var addFieldInc = 0;
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
		if(id!="AddItem" && confirm(buttonpressed)){
			$.ajax({
				type: "POST",
				url: BaseURL+"Scripts/Item.php?action="+buttonpressed,
				data: formData, // serializes the form's elements.
				success: function(data){
					if(buttonpressed=="Remove"){
						console.log(data);
						$('#'+id+'.list_element').remove();
						$('.admin_page').load('Items.php?included');
					} else {
						console.log(data);
						var SerialData = objectifyForm(form.serializeArray());
						$('#'+id+'.list_element').find('.header').text(SerialData.name);
						//alert(SerialData.name);
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
	$('.category_chooser').change(function(){
		var chosen = $(this).val();
		var Fields = CategoryFields[chosen];
		console.log(Fields);
		
		row = $(this).closest('tr');
		table = row.parent();
		table.find('.category_content').remove();
		fieldTable = table.find('.FieldTable');
		Fields.forEach(function(element){
			if(element.type == 'calculation'){
				fieldTable.append(fields_calc(element, ""));
			} else if(element.type == 'largetext'){
				fieldTable.append(fields_large(element, ""));
			} else if(element.type == 'smalltext'){
				fieldTable.append(fields_small(element, ""));
			} else if(element.type == 'image'){
				fieldTable.append(fields_image(element, ""));
			}
		});
	});
	$('.removeFieldButton').click(fieldRemoveAction);
});

function createFirst(){
	$(".empty_view").hide();
	$("#AddItemDiv").find('.content').show();
	$("#AddItemDiv").show();
}

function addItem(){
	form = $('.AddItemForm');
	var formData = form.serialize();
	if(confirm("Add")){
		$.ajax({
			type: "POST",
			url: BaseURL+"Scripts/Item.php?action=Add",
			data: formData,
			success: function(data){
				console.log(data);
				$('.admin_page').load('Items.php?included');
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

function fields_calc(data, value){
	return "\
		<tr class='category_content'>\
			<td>" + data.field + "</td>\
			<td>Calculation</td>\
			<td>\
				<input type='text' name='" + data.id + "' value='" + value + "'>\
			</td>\
		</tr>";
}
function fields_large(data, value){
	return "\
		<tr class='category_content'>\
			<td>" + data.field + "</td>\
			<td>Large Text</td>\
			<td>\
				<input type='text' name='" + data.id + "' value='" + value + "'>\
			</td>\
		</tr>";
}
function fields_small(data, value){
	return "\
		<tr class='category_content'>\
			<td>" + data.field + "</td>\
			<td>Small Text</td>\
			<td>\
				<input type='text' name='" + data.id + "' value='" + value + "'>\
			</td>\
		</tr>";
}
function fields_image(data, value){
	return "\
		<tr class='category_content'>\
			<td>" + data.field + "</td>\
			<td>Image</td>\
			<td>\
				<input type='text' name='" + data.id + "' value='" + value + "'>\
			</td>\
		</tr>";
}

function objectifyForm(formArray) {
	var returnArray = {};
	for (var i = 0; i < formArray.length; i++){
		returnArray[formArray[i]['name']] = formArray[i]['value'];
	}
	return returnArray;
}