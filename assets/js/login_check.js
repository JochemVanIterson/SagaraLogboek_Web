$(document).ready(function() {
	var errorView = $(".error_view");
	$("#login_button").click(function(){
		var form_data = getFormObj('login_data');
		if(form_data.user_login == ""){
			errorView.text("User Empty");
			errorView.show();
			return;
		}
		if(form_data.password_login == ""){
			errorView.text("Password Empty");
			errorView.show();
			return;
		}
		errorView.hide();
		attempt_login();
	});
	
	$(window).keydown(function(event){
		if(event.keyCode == 13) {
			event.preventDefault();
			attempt_login();
			return true;
		}
	});
});

function getFormObj(formId) {
    var formObj = {};
    var inputs = $('#'+formId).serializeArray();
    $.each(inputs, function (i, input) {
        formObj[input.name] = input.value;
    });
    return formObj;
}

function attempt_login(){
	var form_data = getFormObj('login_data');
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			var response = JSON.parse(this.responseText);
			if(response.login == "failed"){
				console.log("Failed" + this.responseText);
				var errorView = $(".error_view");
				errorView.text(response.message);
				errorView.show();
			} else {
				console.log("Success" + this.responseText);
				setCookie("username", response.data.username, 10);
				setCookie("iv", response.data.iv, 10);
				window.location.href = BaseURL + Refered;
			}
			
		}
	};
	xhttp.open("POST", BaseURL + "Scripts/Login.php", true);
	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	var requestString = "user=" + form_data.user_login + "&pw=" + form_data.password_login + "&raw";
	xhttp.send(requestString);
}