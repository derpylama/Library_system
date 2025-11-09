function toggleView(view) {
    sessionStorage.setItem('currentView', view);
    document.getElementById('all-media-view').classList.add('hidden');
    document.getElementById('my-account-view').classList.add('hidden');
    document.getElementById(view).classList.remove('hidden');

    const savedView = sessionStorage.getItem('currentView') || 'all-media-view';

    var allMediaButton = document.getElementById("all-media-button");
    
    var myAccountButton = document.getElementById("my-account-button");
    if (myAccountButton !== null) {
        if (savedView === 'my-account-view') {
            myAccountButton.style.display = "none";
        }
        else{
            myAccountButton.style.display = "inline-block";
        }
    }    

    if (savedView === 'all-media-view') {
        allMediaButton.style.display = "none";
    }
    else{
        allMediaButton.style.display = "inline-block";
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const savedView = sessionStorage.getItem('currentView') || 'all-media-view';
    toggleView(savedView);

    var allMediaButton = document.getElementById("all-media-button");
    var myAccountButton = document.getElementById("my-account-button");

    if (myAccountButton !== null) {
        if (savedView === 'my-account-view') {
            myAccountButton.style.display = "none";
        }
        else{
            myAccountButton.style.display = "inline-block";
        }
    }  

    if (savedView === 'all-media-view') {
        allMediaButton.style.display = "none";
    }
    else{
        allMediaButton.style.display = "inline-block";
    }

    document.getElementById('all-media-view').addEventListener('click', function() {
        toggleView('all-media-view');
    });

    document.getElementById('my-account-view').addEventListener('click', function() {
        toggleView('my-account-view');
    });

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