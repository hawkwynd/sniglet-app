var xmlHttp
var xmlHttp2
var recordid

/**
 * load inits
 */
$(function () {
    $.get('sniglet_edit.php?func=getStats', function (results) {
        var obj = jQuery.parseJSON(results);
        var totalCountActive = 'Active Sniglets: ' + obj[0].activeCount;
        var verbCount = '<br/>Verbs: ' + obj[0].verbCount;
        var nounCount = '<br/>Nouns: ' + obj[0].nounCount;
        var adjCount = ' <br/>Adjectives: ' + obj[0].adjCount + '<br/>';
        var LastSnigTitle = '<br/>Last Sniglet Added: <i>' + obj[0].sniglet_term + '</i>';
        var LastRecDate = '<br/>Last Date Added: ' + obj[0].sniglet_date;
        $('#listArea').hide();
        $('#statistics').html(totalCountActive + verbCount + nounCount + adjCount + LastRecDate + LastSnigTitle);
    });
});


/**
 * logout()- simple enough function here.
 */

function logout() {
    $.get('sniglet_edit.php?func=logout', function (data) {
        setTimeout(function () {
            window.location = "sniglet_edit.php";
        }, 100);
    });
}

/**
 * fetchAlpha
 * @param letter
 * @return results
 *
 */
function fetchAlpha(letter) {

    var string = '';
    $.get('sniglet_edit.php?func=searchByAlpha&letter=' + letter, function (results) {

        myObj = jQuery.parseJSON(results);

        string = '<div class="listing_header"><p>' + myObj.length + ' sniglets found starting with the letter "' + letter + '"</p></div>';

        $.each(myObj, function (key, value) {
            string += '<div class="listing"><span class="lk" id=' + value.sniglet_id +'>' + value.sniglet_term + '</span> ' +
                '(<i>' + value.sniglet_phonetics + '</i>)' +
                    value.sniglet_type + ' - ' +
                    value.sniglet_definition +
                '</div>';
        });

        $('#resultArea').hide();
        $('#listArea').html(string).show();

        // Set listerner on our new list, for editing
        $('.lk').click( function() {
            // alert('You clicked on ID ' + $(this).attr('id') );
            var id = $(this).attr('id');

            loadEditor(id);

        } );
    });


}


function loadEditor(id) {


    console.log('Load editor calling for id ' + id);

    $.post('sniglet_edit.php', {func:"load", id: id}, function( data) {

        console.log(data);

        var myjson = $.parseJSON(data);

        $("#SnigletTxt").html('<b>' + myjson[0]['sniglet_term'] +'</b>' )

            .append(' (<i>' + myjson[0]['sniglet_phonetics'] +'</i>) ' + myjson[0]['sniglet_type'] + '<br/>')
            .append(myjson[0]['sniglet_definition'] +'<br/>');
    });
}


function list() {
    $.post('sniglet_edit.php?func=list', function (data) {
        obj = jQuery.parseJSON(data);

        var string = '<dv class="listing_header"><p>Listing all ' + obj.length + ' records.</p></div>';

        $.each(obj, function (key, value) {
            string += '<div class="listing"><span class="lk" id=' + value.sniglet_id +'>' + value.sniglet_term + '</span> ' +
                '(<i>' + value.sniglet_phonetics + '</i>)' +
                value.sniglet_type + ' - ' +
                value.sniglet_definition +
                '</div>';

        });


        $('#header').fadeOut('slow');
        $('#resultArea').fadeOut();
        $('#searchTxt').val('');
        $('#listArea').fadeIn().html(string);

        // Set listerner on our new list, for editing
        $('.lk').click( function() {
            // alert('You clicked on ID ' + $(this).attr('id') );
            var id = $(this).attr('id');

            loadEditor(id);

        } );
    });
}

function showIt() {
    $('#header').fadeIn('slow');
}
function hideHeader() {
    $('#header').fadeOut('slow');
}

/**
 *
 * @return {Boolean}
 */
function search() {
    if ($('#searchTxt').val().length < 1) {
        alert('Please enter something to search for.');
        $('#searchTxt').focus();
        return false;
    }

    var searchTxt = $('#searchTxt').val();
    $.getJSON('sniglet_edit.php?func=searchSniglet&searchTxt=' + searchTxt, function (result) {
        var string = '';
        if (result.length > 0) {
            string += '<p>' + result.length + ' matches found for "<b>' + searchTxt + '</b>"</p>';
            $.each(result, function (i, sniglet) {

                string += '<div class="listing"><b>' + sniglet.sniglet_term + '</b> (<i>' + sniglet.sniglet_phonetics + '</i>) ';
                string += sniglet.sniglet_type + ' - ' + sniglet.sniglet_definition + '</div>';

            });
        } else {
            string = '<h3>No records found for search:  "' + searchTxt + '"</h3>';
        }
        $('#listArea').fadeOut();
        $('#resultArea').fadeIn("slow").html(string);


    });
}

/**
 *
 * @return {Boolean}
 */
function save_sniglet() {
    $('#resultArea').fadeOut();
    $('#savebtn').val('Saving..');

    if ($('#sniglet_term').val().length < 1) {
        alert('No Term found!');
        $('#sniglet_term').focus();
        $('#savebtn').val('Saving Sniglet')
        return false;
    }

    if ($('#sniglet_definition').val().length < 3) {
        alert('No definition found!');
        $('#sniglet_definition').focus();
        return false;
    }

    var items = 'func=save&sniglet_term=' + $('#sniglet_term').val() + '&sniglet_phonetics=' + $('#sniglet_phonetics').val() + '&sniglet_type=' + $('#sniglet_type').val() + '&sniglet_definition=' + $('#sniglet_definition').val();

    $.post('sniglet_edit.php', items, function (data) {

        obj = jQuery.parseJSON(data);

        if (obj.ID > 0) {
            $('#listArea').hide();
            var success = '<h3 id="success">Successful addition!</h3>';
            $('#resultArea').show().html(success + obj.sniglet_term + ' (' + obj.sniglet_type + ') - <i>' + obj.sniglet_phonetics + '</i>' + '<p>' + obj.sniglet_definition + '</p>');

            setTimeout(function () {
                window.location = "sniglet_edit.php";
            }, 3000);

        } else { // Duplicate found! Throw error message here.


            var searchTxt = $('#sniglet_term').val();

            $.getJSON('sniglet_edit.php?func=searchSniglet&searchTxt=' + searchTxt, function (result) {
                var string = '';

                string += '<h3 id="error">' + searchTxt + ' already exists in the Sniglet Archives!</h3>';
                $.each(result, function (i, sniglet) {

                    string += '<div class="listing"><b>' + sniglet.sniglet_term + '</b> (<i>' + sniglet.sniglet_phonetics + '</i>) ';
                    string += sniglet.sniglet_type + ' - ' + sniglet.sniglet_definition + '</div>';

                });

                $('#listArea').hide();
                $('#resultArea').fadeIn("slow").html(string);
                $('#savebtn').val('Save Sniglet');
                console.log(result);


            });

        }
    });
}


function getSniglet() {

   xmlHttp = GetXmlHttpObject()
    if (xmlHttp == null) {
        alert("Browser does not support HTTP Request")
        return
    }
    var url = "/sniglets/index.php"
    xmlHttp.onreadystatechange = SnigletstateChanged
    xmlHttp.open("GET", url, true)
    xmlHttp.send(null)
}
/**
 *
 * @constructor
 */
function SnigletstateChanged() {
    if (xmlHttp.readyState == 4 || xmlHttp.readyState == "complete") {

        var json = JSON.parse(xmlHttp.responseText);
        var recid = json.sniglet_id

        // update the element from returned json array
        // json.sniglet_id => Id of the row
        // json.sniglet_term => Term of Sniglet
        // json.sniglet_phonetics => Phonetics spelling
        // json. sniglet_type => n, v, adj.
        // json.sniglet_definition => Verbage definition

        var snigletText = "<b>" + json.sniglet_term + "</b> (<i>" + json.sniglet_phonetics + "</i>) " + "-" + json.sniglet_type + ". "
            + json.sniglet_definition;

        $("#SnigletTxt").html(snigletText);
        // display sniglet results

        $('#creds').html('<a href="javascript: logout()")>Logout</a>');
        $('#login').html('<a href="login.php">login</a>')
    }
}


/**
 *
 * @return {*}
 * @constructor
 */
function GetXmlHttpObject() {
    var objXMLHttp = null

    if (window.XMLHttpRequest) {
        objXMLHttp = new XMLHttpRequest()
    }
    else if (window.ActiveXObject) {
        objXMLHttp = new ActiveXObject("Microsoft.XMLHTTP")
    }
    return objXMLHttp
} 






	