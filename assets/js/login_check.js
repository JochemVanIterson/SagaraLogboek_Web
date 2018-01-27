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
		aes128cdc(form_data.password_login, key, "ahd3aal7z71zjm2h");
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

function aes128cdc(data, key, iv){
	// this is Base64 representation of the Java counterpart
	//var base64Key = btoa(key);
	//console.log( "base64Key = " + base64Key );
	//
	//var ByteKey = CryptoJS.enc.Utf16.parse(key);
	//console.log("ByteKey = " + ByteKey);
	//
	//// this is the actual key as a sequence of bytes
	//console.log( "key = " + key );
	//var key = ByteKey;
	//
	//var base64IV = btoa(iv);
	//console.log( "base64IV = " + base64IV );
	//
	//var ByteIV = CryptoJS.enc.Utf16.parse(iv);
	//console.log("ByteIV = " + ByteIV);
	//
	//// this is the actual key as a sequence of bytes
	//console.log( "iv = " + iv );
	//var iv = ByteIV;

	// this is the plain text
	console.log( "plaintText = " + data );
	
	var key = CryptoJS.lib.WordArray.random(16);
	console.log( "key = " + key);
	key = toHex("db58gdt6x113j0da");
	console.log( "key = " + key);

	var iv  = CryptoJS.lib.WordArray.random(16);
	console.log( "iv = " + iv);
	iv = toHex("ahd3aal7z71zjm2h");
	console.log( "iv = " + iv);

	var encryptedData = CryptoJS.AES.encrypt(data, key, { iv: iv });
	var decryptedData = CryptoJS.AES.decrypt("SNn8C2p2it3syIJQVnUp4w==", key, { iv: iv });
	
	// this is Base64-encoded encrypted data
	//var encryptedData = CryptoJS.AES.encrypt(data, key, {
	//    iv: iv,
	//    mode: CryptoJS.mode.CBC,
	//    padding: CryptoJS.pad.Pkcs7
	//});
	console.log( "encryptedData = " + encryptedData);
	
	// this is the decrypted data as a sequence of bytes
	//var decryptedData = CryptoJS.AES.decrypt( encryptedData, key, {
	//	iv: iv,
	//    mode: CryptoJS.mode.CBC,
	//    padding: CryptoJS.pad.Pkcs7
	//} );
	console.log( "decryptedData = " + decryptedData );
	
	// this is the decrypted data as a string
	var decryptedText = decryptedData.toString( CryptoJS.enc.Utf8 );
	console.log( "decryptedText = " + decryptedText );
}

function toHex(s) {
    // utf8 to latin1
    var s = unescape(encodeURIComponent(s))
    var h = ''
    for (var i = 0; i < s.length; i++) {
        h += s.charCodeAt(i).toString(16)
    }
    return h
}

//function aes_encrypt(message, key){
//	var iv  = randomString(16); //length=22
//	var iv = "kjr4hvsa387585s2";
//	
//	//key = CryptoJS.enc.Base64.parse(key); // length=16 bytes
//	//iv = CryptoJS.enc.Base64.parse(iv); // length=16 bytes
//	
//	//var key = CryptoJS.enc.Hex.parse(key);
//	//var iv = CryptoJS.enc.Hex.parse(iv);
//	
//	var cipherData = CryptoJS.AES.encrypt(padString(message), key, {
//		iv: iv,
//		mode: CryptoJS.mode.CBC,
//		keySize: 256 / 32,
//		padding: CryptoJS.pad.Pkcs7
//	});
//	
//	//var cipherDataEnc = CryptoJS.enc.Base64.parse(padString(cipherData));
//	var cipherDataEnc = "";
//	alert(
//		"key: "+key+"\n"+
//		"iv: "+iv+"\n"+
//		"message: "+message+"\n"+
//		"cipherData: "+cipherData+"\n"+
//		"cipherDataEnc: "+cipherDataEnc);
//	
//	return({data: cipherData, iv: iv});
//}
//
//function aes_decrypt(data, key, iv){
//	//key = CryptoJS.enc.Base64.parse(key); // length = 16 bytes
//	//iv = CryptoJS.enc.Base64.parse(iv); // length = 16 bytes
//	
//	var decryptedData = CryptoJS.AES.decrypt(data, key, { iv: iv });
//	return decryptedData;
//}

function randomString(size) {
	var text = "";
	var possible = "abcdefghijklmnopqrstuvwxyz0123456789";
	
	for (var i = 0; i < size; i++)
		text += possible.charAt(Math.floor(Math.random() * possible.length));
	
	return text;
}

function padString(source) {
    var paddingChar = ' ';
    var size = 16;
    var padLength = size - source.length;

    for (var i = 0; i < padLength; i++) source += paddingChar;

    return source;
}

//function encode(message, key){
//	var iv = "kjr4hvsa387585s2";
//	var message = padString(message);
//	//var iv = CryptoJS.enc.Hex.parse(iv);
//
//	var cipher = CryptoJS.AES.encrypt(message, key, {
//	    iv: iv,
//	    mode: CryptoJS.mode.CBC,
//	    padding: CryptoJS.pad.Pkcs7
//	});
//
//	var cipherDataEnc = cipher.ciphertext.toString(CryptoJS.enc.Base64);
//
//	alert(
//		"key: "+key+" "+key.length+"\n"+
//		"iv: "+iv+" "+iv.length+"\n"+
//		"message: "+message+" "+message.length+"\n"+
//		"cipherData: "+cipher.ciphertext+"\n"+
//		"cipherDataEnc: "+cipherDataEnc+"\n");
//
//	//cipherBase64 = cipher.ciphertext.toString().hex2a().base64Encode();
//	return cipherData;
//}