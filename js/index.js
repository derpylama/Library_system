function toggleView(view) {
    sessionStorage.setItem('currentView', view);
    
    const allMediaViewBtn = document.getElementById('all-media-view')
    if (allMediaViewBtn) allMediaViewBtn.classList.add('hidden');

    const myAccountViewBtn = document.getElementById('my-account-view')
    if (myAccountViewBtn) myAccountViewBtn.classList.add('hidden');

    const viewElem = document.getElementById(view);
    if (viewElem) viewElem.classList.remove('hidden');

    const savedView = sessionStorage.getItem('currentView') || 'all-media-view';

    var allMediaButton = document.getElementById("all-media-button");
    
    var myAccountButton = document.getElementById("my-account-button");
    if (myAccountButton !== null) {
        if (savedView === 'my-account-view') {
            //myAccountButton.style.display = "none";
            myAccountButton.classList.add('hidden');
        }
        else {
            //myAccountButton.style.display = "inline-block";
            myAccountButton.classList.remove('hidden');
        }
    }    

    if (savedView === 'all-media-view') {
        //allMediaButton.style.display = "none";
        allMediaButton.classList.add('hidden');
    }
    else{
        //allMediaButton.style.display = "inline-block";
        allMediaButton.classList.remove('hidden');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // If URI has ?clearsessiontab, clear the session storage for currentView and remove ?clearsessiontab
    if (window.location.search.includes('clearsessiontab')) {
        sessionStorage.removeItem('currentView');
        var newUrl = window.location.href.split('?')[0];
        window.history.replaceState({}, document.title, newUrl);
    }

    // Handle tabbing
    const savedView = sessionStorage.getItem('currentView') || 'all-media-view';
    toggleView(savedView);

    var allMediaButton = document.getElementById("all-media-button");
    var myAccountButton = document.getElementById("my-account-button");

    if (myAccountButton !== null) {
        if (savedView === 'my-account-view') {
            //myAccountButton.style.display = "none";
            myAccountButton.classList.add('hidden');
        }
        else{
            //myAccountButton.style.display = "inline-block";
            myAccountButton.classList.remove('hidden');
        }
    }  

    if (savedView === 'all-media-view') {
        //allMediaButton.style.display = "none";
        allMediaButton.classList.add('hidden');
    }
    else{
        //allMediaButton.style.display = "inline-block";
        allMediaButton.classList.remove('hidden');
    }

    //document.getElementById('all-media-view').addEventListener('click', function() {
    const allMediaViewBtn2 = document.getElementById('all-media-view');
    if (allMediaViewBtn2) {
        allMediaViewBtn2.addEventListener('click', function() {
            toggleView('all-media-view');
        });
    }

    //document.getElementById('my-account-view').addEventListener('click', function() {
    const myAccountViewBtn2 = document.getElementById('my-account-view');
    if (myAccountViewBtn2) {
        myAccountViewBtn2.addEventListener('click', function() {
            toggleView('my-account-view');
        });
    }

    var mediaLoanButtons = document.querySelectorAll(".media-loan-button");

    mediaLoanButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            window.scrollTo(0, 0);

            var messageText = document.getElementById('top-menu-message');
            messageText.style.display = "block";
            messageText.innerText = "You need to be logged in to be able to loan media";

        });
    });
})