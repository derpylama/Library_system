function toggleView(view) {
    sessionStorage.setItem('currentView', view + '-view');
    document.getElementById('media-view').classList.add('hidden');
    document.getElementById('loans-view').classList.add('hidden');
    document.getElementById(view + '-view').classList.remove('hidden');

    const savedView = sessionStorage.getItem('currentView') || 'media-view';

    var allMediaButton = document.getElementById("all-media-button");
    
    var myAccountButton = document.getElementById("my-account-button");
    if (myAccountButton !== null) {
        if (savedView === 'loans-view') {
            myAccountButton.style.display = "none";
        }
        else{
            myAccountButton.style.display = "inline-block";
        }
    }    

    if (savedView === 'media-view') {
        allMediaButton.style.display = "none";
    }
    else{
        allMediaButton.style.display = "inline-block";
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const savedView = sessionStorage.getItem('currentView') || 'media-view';
    toggleView(savedView.replace('-view', ''));

    var allMediaButton = document.getElementById("all-media-button");
    var myAccountButton = document.getElementById("my-account-button");

    if (myAccountButton !== null) {
        if (savedView === 'loans-view') {
            myAccountButton.style.display = "none";
        }
        else{
            myAccountButton.style.display = "inline-block";
        }
    }  

    if (savedView === 'media-view') {
        allMediaButton.style.display = "none";
    }
    else{
        allMediaButton.style.display = "inline-block";
    }


    document.getElementById('media-view').addEventListener('click', function() {
        toggleView('media');
    });

    document.getElementById('loans-view').addEventListener('click', function() {
        toggleView('loans');
    });
})