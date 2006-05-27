function edit_category(obj) {
	document.getElementById('l-top-' + obj.id).innerHTML = 
	'<input type="text" id="new_cat-' + obj.id + '" name="new_cat-' + obj.id + 
	'" tabindex="1" size="25" value="' + document.getElementById('label1-' + 
	obj.id).innerHTML + '"/><label for="new_parent-' + obj.id + '">Hija de:</label>' + 
	'<input type="text" id="new_parent-' + obj.id + '" name="new_parent-' + 
	obj.id + '" tabindex="1" size="1" value="' + document.getElementById('parent-' + 
	obj.id).value +'"/>';

	// Para ocultar submit principal y sacar uno único por cada campo.
	// Habilitar esta opción anularia la posibilidad de actualización
	// múltiple de categorías (sólo sería actualizable la categoría
	// asociada al botón de envío).
	// <input type="submit" name="new_category" value="' + document.getElementsByName('new_category')[0].value + '" class="genericsubmit">';
	// document.getElementById('submit-p').innerHTML = "";

	document.getElementById('insert-p').innerHTML = "";
	return;
}
