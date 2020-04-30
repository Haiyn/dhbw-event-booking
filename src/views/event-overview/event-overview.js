let dateFrom;
let dateTo;

$(document).ready(function () {
    $('.datepicker-from').datepicker({
        format: 'yyyy-mm-dd'
    }).on("changeDate", function (value) {
        dateFrom = new Date(value.date);
        filterDate();
    });
    $('.datepicker-to').datepicker({
        format: 'yyyy-mm-dd'
    }).on("changeDate", function (value) {
        dateTo = new Date(value.date);
        filterDate();
    });
    $('#event-overview-search').on('keyup', function () {
        // Get the search value and put it to lower case
        const value = $(this).val().toLowerCase();
        // Filter all cards with the event-overview-filter class
        $('.event-overview-filter').filter(function () {
            // Get the title and location of the card
            const title = this.querySelector('#event-overview-card-title');
            const location = this.querySelector('#event-overview-card-location');
            if (title != null) {
                // Toggle the visibility of the card based on the match of the title/ location
                // and the searched value
                const toggle = $(title).text().toLowerCase().indexOf(value) > -1 ? true :
                    $(location).text().toLowerCase().indexOf(value) > -1;
                $(this).toggle(toggle);
            }
        });
    });
});

/**
 * Filter all cards by date
 */
function filterDate()
{
    $('.event-overview-filter').filter(function () {
        // Get the item with the event date
        const date = this.querySelector('#event-overview-card-date');
        // Check if date exists and toggle card based on the date
        if (date) {
            const eventDate = new Date($(date).text().toString());
            $(this).toggle(eventDate >= (dateFrom ? dateFrom : 0) && eventDate <= (dateTo ? dateTo : Number.MAX_SAFE_INTEGER));
        }
    });
}
