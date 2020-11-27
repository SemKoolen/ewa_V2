import '../css/base/home.css';
import $ from 'jquery';


window.onload = (event) => {
    document.getElementById("PrivacyVerklaringLink").addEventListener("mouseenter", function ( event ){
        // $("#privacyVerklaringPopUp").toggleClass("show")
        $(".popuptext").toggleClass("show")
    });
    
    document.getElementById("PrivacyVerklaringLink").addEventListener("mouseleave", function ( event ){
        // $("#privacyVerklaringPopUp").toggleClass("show")
        $(".popuptext").toggleClass("show")
    });
    console.log('page is fully loaded');
};


