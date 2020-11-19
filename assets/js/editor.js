/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)


// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
require('jquery');
require('../ckeditor/ckeditor.js')
require('../ckeditor/adapters/jquery.js'); 


// Add the following code if you want the name of the file appear on select
$(".custom-file-input").on("change", function() {
    var fileName = $(this).val().split("\\").pop();
    $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
    debugger;
    var fileName = '{{ document.file }}'.split("\\").pop();
    $(".custom-file-input").siblings(".custom-file-label").addClass("selected").html(fileName);

console.log('Hello Webpack Encore! Edit me in assets/js/app.js');