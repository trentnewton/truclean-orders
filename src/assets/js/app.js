// import $ from 'jquery';
import 'what-input';
// import hmacSHA256 from 'crypto-js/hmac-sha256';
// import Base64 from 'crypto-js/enc-base64';



// Foundation JS relies on a global variable. In ES6, all imports are hoisted
// to the top of the file so if we used `import` to import Foundation,
// it would execute earlier than we have assigned the global variable.
// This is why we have to use CommonJS require() here since it doesn't
// have the hoisting behavior.
// window.jQuery = $;
// require('foundation-sites');

// If you want to pick and choose which modules to include, comment out the above and uncomment
// the line below
//import './lib/foundation-explicit-pieces';
// import './lib/hmac-sha256';
// import './lib/enc-base64-min';


// $(document).foundation();

// setInterval(function(){
//     window.location.reload(1);
//  }, 30000);


//  var captionHeight = document.getElementsByClassName('fixed-caption'). offsetHeight;

//  document.getElementsByClassName('fixed-thead').style.top = captionHeight+'px';

// document.addEventListener("DOMContentLoaded", function(event) {
//     // window.scrollTo(0,document.body.scrollHeight);
//     // function replace() {
//     //     const replaces = document.querySelectorAll('.replacetext');
//     //     const sources = document.querySelectorAll('.sourcetext');

//     //     replaces.forEach(e => {
//     //         const source = '.sourcetext.'+e.classList[1];
//     //         e.innerText = document.querySelector(source).innerText;
//     //     });
//     // }
// });

// var delay=100 * 6;//1*6 seconds
// setTimeout(function(){
//     document.addEventListener("DOMContentLoaded", function(event) {
//         window.scrollTo(0,document.body.scrollHeight);
//     });
// },delay);



function replace() {
    const replaces = document.querySelectorAll('.replacetext');
    const sources = document.querySelectorAll('.sourcetext');

    replaces.forEach(e => {
        const source = '.sourcetext.'+e.classList[1];
        e.innerText = document.querySelector(source).innerText;
    });
}
document.addEventListener("DOMContentLoaded", replace);

// window.scrollTo(0,document.body.scrollHeight);



document.addEventListener("DOMContentLoaded", function(event) {
    // window.scrollTo(0,document.body.scrollHeight);
    // window.scrollTo(0,document.body.scrollHeight);
    setTimeout(function (){location.reload()},30000);

});

// function pageScroll() {
//     window.scrollBy(0,50); // horizontal and vertical scroll increments
//     scrolldelay = setTimeout('pageScroll()',100); // scrolls every 100 milliseconds
// }