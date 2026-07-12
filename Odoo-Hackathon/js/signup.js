const form = document.querySelector("form");

form.addEventListener("submit",function(e){

    const password = document.querySelectorAll("input[type='password']");

    if(password[0].value !== password[1].value){

        e.preventDefault();

        alert("Passwords do not match!");

    }

});