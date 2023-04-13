$(document).ready(function () {
    var btnFinish = $('<button></button>').text('Valider')
        .addClass('btn btn-info')
        .on('click', function () {
            submit();
        });
    // Smart Wizard
    $('#smartwizard').smartWizard({
        selected: 0,
        theme: 'arrows',
        transition: {
            animation: 'slide-swing', // Effect on navigation, none/fade/slide-horizontal/slide-vertical/slide-swing
        },
        toolbarSettings: {
            toolbarExtraButtons: [btnFinish]
        },
        lang: { // Language variables for button
            next: 'Suivant',
            previous: 'Précedent'

        }
    });

});