<?php
session_start();

// Check if the user is logged in (adjust this according to your authentication mechanism)
if (!isset($_SESSION['user_email'])) {
    header("location: ../not_logged_in.html");
}

// echo $_SESSION['user_email'];

$servername = "localhost";
$username = "root";
$password = "";
$database = "loginpage";
$conn = new mysqli($servername, $username, $password, $database);

try {
    $connl = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $connl->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch events for the logged-in user
    $user_email = $_SESSION['user_email'];
    $stmt = $connl->prepare("SELECT id, event_title, start_date, end_date, allDay, event_link, color  FROM calendar WHERE user_email = :user_email");
    $stmt->bindParam(':user_email', $user_email);
    $stmt->execute();

    $events = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $startDateTime = new DateTime($row['start_date']);
        $endDateTime = new DateTime($row['end_date']);

        $formattedStart = $startDateTime->format('Y-m-d\TH:i:s');
        $formattedEnd = $endDateTime->format('Y-m-d\TH:i:s');

        $event = new stdClass();
        $event->id = $row['id'];
        $event->title = $row['event_title'];
        $event->start = $formattedStart;
        $event->end = $formattedEnd;
        // $event->allDay = $row['allDay'];
        $event->url = $row['event_link'];
        $event->color = $row['color'];

        $events[] = $event;
    }

    // echo json_encode($events);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$userEmail = $_SESSION['user_email'];
$details = mysqli_query($conn, "SELECT * FROM `details` WHERE user_email = '$userEmail'");
$detail_rows = mysqli_fetch_array($details, MYSQLI_ASSOC);
$profile_picture = $detail_rows['imgurl'];

?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset='utf-8' />
    <meta name="description" content="Schedule Events using the integrated Calendar">
    <meta property="og:image" content="../../images/OG-images/calendar-meta.png">

    <title>Calendar</title>
    <link rel="icon" href="../../images/calendar/favicon.ico" type="image/x-icon">

    <!-- scripts necessary to run fullcalendar -->
    <script src="
https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js
"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@6.1.9/index.global.min.js"></script>
    <script src='fullcalendar/dist/index.global.js'></script>

    <!-- tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- icons by font-awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.9/css/all.min.css">

    <!-- flowbite -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.8.1/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.8.1/flowbite.min.js"></script>

    <!-- datepicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.8.1/datepicker.min.js"></script>

    <!-- sweet alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="sweetalert2.all.min.js"></script>

    <!-- swal themes -->
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
</head>

<body>
    <div class="bg-cover bg-center overflow-hidden h-screen w-screen" style="background-image: url('<?php echo $profile_picture ?>');">
        <div class="flex justify-between h-full py-3">

            <!-- this is the side nav, do not touch -->
            <?php include '../partials/nav.php' ?>

            <div class="ml-0 container grid grid-cols-9 grid-rows-5 gap-4 h-full w-screen mx-auto my-auto">
                <div class="col-span-2 row-span-5 w-full h-full rounded-xl pr-4 bg-gray-700 bg-opacity-50 p-4 flex flex-col space-y-3 z-50" style="backdrop-filter: blur(8px);">
                    <div class="h-5/6 w-full">
                        <div class="bg-gray-700 shadow-xl bg-opacity-50 h-full rounded-t-2xl flex flex-col">
                            <div class="h-1/6 w-full flex flex-col items-center justify-center bg-white rounded-t-xl">
                                <h1 class="text-center text-gray-900 text-xl font-sans">Upcoming Events</h1>
                            </div>
                            <div id="calendarbgday" class="col-span-7 col-start-3 row-span-5 h-full shadow-2xl bg-cover bg-center">
                                <div id="daycalendar" class="w-full font-sans h-full text-xl bg-gray-700 bg-opacity-50">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class=" h-1/6 rounded-2xl">
                        <button id="changebgbtn" class="block text-gray-200 text-sm md:text-base lg:text-lg xl:text-xl 2xl:text-2xl 3xl:text-3xl 4xl:text-4xl 5xl:text-5xl 6xl:text-6xl text-center  font-sans p-8 pt-4 pb-4 rounded-xl  hover:opacity-100 transition ease-in-out hover:shadow-2xl space-y-12 bg-gray-700 shadow-lg hover:shadow hover:text-white hover:bg-gray-700 bg-opacity-30 transform duration-100 hover:scale-90 w-full text-center h-full hover:shadow-2xl font-medium" type="button" onclick="changebg()">
                            Change BG
                        </button>
                    </div>

                    <!-- Modal toggle -->
                    <div class="h-1/6 rounded-2xl">
                        <button data-modal-target="new-event-modal" data-modal-toggle="new-event-modal" class="block text-gray-200 text-sm md:text-base lg:text-lg xl:text-xl 2xl:text-2xl 3xl:text-3xl 4xl:text-4xl 5xl:text-5xl 6xl:text-6xl text-center  font-sans p-8 pt-4 pb-4 rounded-xl  hover:opacity-100 transition ease-in-out hover:shadow-2xl space-y-12 bg-gray-700 shadow-lg hover:shadow hover:text-white hover:bg-gray-700 bg-opacity-30 transform duration-100 hover:scale-90 w-full text-center h-full hover:shadow-2xl font-medium" type="button">
                            Create
                        </button>
                    </div>
                </div>

                <!-- Main modal -->
                <div id="new-event-modal" class="transition transform fixed top-0 left-0 right-0 z-50 hidden h-full w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full" style="backdrop-filter: blur(8px);">
                    <div class="relative w-full max-w-2xl max-h-full">
                        <!-- Modal content -->
                        <div class="relative shadow-2xl rounded-lg shadow bg-gray-950 bg-opacity-30 backdrop-blur-2xl">
                            <button type="button" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="new-event-modal">
                                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                </svg>
                                <span class="sr-only">Close modal</span>
                            </button>
                            <div class="px-6 py-6 lg:px-8">
                                <h3 class="mb-8 text-4xl font-semibold text-center text-gray-900 text-white">Add
                                    event details
                                </h3>
                                <hr class="my-4 border-gray-600">
                                <form class="space-y-6" action="insert_event.php" method="POST">
                                    <!-- title -->
                                    <div>
                                        <label for="event_title" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Title</label>
                                        <input type="text" name="event_title" id="event_title" class="shadow-indigo-500/100 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-300 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" placeholder="Enter your event title here" required>
                                    </div>

                                    <hr class="my-4 border-gray-600">

                                    <!-- date time select -->
                                    <div class="flex flex-col w-full space-y-3">
                                        <!-- start -->
                                        <label for="startDate" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Start</label>
                                        <div class="flex flex-row space-x-3 w-full h-full">
                                            <input type="date" name="startDate" id="startDate" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-300 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                                            <input type="time" name="startTime" id="startTime" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-300 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                                        </div>
                                        <!-- end -->
                                        <label for="endDate" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">End</label>
                                        <div class="flex flex-row space-x-3 w-full h-full">
                                            <input type="date" name="endDate" id="endDate" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-300 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                                            <input type="time" name="endTime" id="endTime" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-300 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                                        </div>
                                    </div>

                                    <!-- dropdown for color selection -->
                                    <div class="flex flex-col w-full space-y-3">
                                        <label for="color" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Color</label>
                                        <div class="relative inline-block w-full text-gray-700">
                                            <select id="color" name="color" class="w-full h-10 pl-3 pr-6 text-base placeholder-gray-600 border rounded-lg appearance-none focus:shadow-outline bg-gray-50 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                                                <option class="bg-red-500 " value="red">Red</option>
                                                <option class="bg-yellow-500 " value="#a5b00b">Yellow</option>
                                                <option class="bg-green-500 " value="green">Green</option>
                                                <option class="bg-blue-500 " value="blue">Blue</option>
                                                <option class="bg-indigo-500 " value="indigo">Indigo</option>
                                                <option class="bg-purple-500 " value="purple">Purple</option>
                                                <option class="bg-pink-500 " value="pink">Pink</option>
                                                <option class="bg-gray-500 " value="gray">Gray</option>
                                            </select>
                                        </div>

                                        <hr class="my-4 border-gray-600">

                                        <!-- desc -->
                                        <!-- <label for="eventDescription"
                                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Event
                                            Description</label>
                                        <textarea id="eventDescription" rows="4" name="eventDescription"
                                            class="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-300 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                            placeholder="Enter Event Description here ..."></textarea> -->

                                        <div>
                                            <label for="eventLink" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Event
                                                URL</label>
                                            <input type="url" name="eventLink" id="eventLink" placeholder="https://example.com" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-300 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white">
                                        </div>

                                        <input type="submit" name="submit" id="submit" class="block text-gray-900 text-sm md:text-base lg:text-lg xl:text-xl 2xl:text-2xl 3xl:text-3xl 4xl:text-4xl 5xl:text-5xl 6xl:text-6xl text-center  font-sans p-8 pt-4 pb-4 rounded-xl  hover:opacity-100 transition ease-in-out hover:shadow-2xl space-y-12 bg-indigo-500 shadow-lg hover:shadow-indigo-500/100 hover:text-indigo-950 hover:bg-indigo-300 transform duration-100 hover:scale-105 w-full text-center h-full hover:shadow-2xl font-semibold" />
                                </form>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
            <div id="calendarbg" class="col-span-7 col-start-3 row-span-5 mr-4 rounded-2xl shadow-2xl bg-cover bg-center">
                <div id='calendar' class=" w-full font-sans font-light h-full bg-gray-700 text-white text-shadow-2xl p-4 rounded-2xl bg-opacity-50 text-xl font-bold text-gray-950 shadow-2xl" style="backdrop-filter: blur(8px);"></div>
            </div>
        </div>
    </div>


    <script>
        // retrieve events from database
        console.log(<?php echo json_encode($events) ?>);
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var user_email = "deeptejdhauskar2003@gmail.com";
            console.log(user_email);
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    start: 'prev,next today',
                    center: 'title',
                    end: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                selectable: true,
                navLinks: true,
                timeZone: 'local',
                events: <?php echo json_encode($events); ?>,
                eventClick: function(info) {
                    info.jsEvent.preventDefault();

                    console.log(info.event.id);
                    const swalOptions = {
                        icon: 'info',
                        title: info.event.title,
                        html: 'Starts: ' + info.event.start.toLocaleString(undefined, {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric',
                                hour: 'numeric',
                                minute: 'numeric'
                            }) + '<br>' +
                            'Ends: ' + info.event.end.toLocaleString(undefined, {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric',
                                hour: 'numeric',
                                minute: 'numeric'
                            }) + '<br><br>',
                        showCancelButton: true,
                        cancelButtonText: 'Delete Event',
                        cancelButtonColor: '#d33',
                        showCloseButton: true,

                    };

                    if (info.event.url) {
                        swalOptions.showConfirmButton = true;
                        swalOptions.confirmButtonText = 'Event Link';
                        swalOptions.confirmButtonColor = '#3085d6';
                    }

                    Swal.fire(swalOptions).then((result) => {
                        if (result.isConfirmed && info.event.url) {
                            window.open(info.event.url, '_blank');
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            const eventId = info.event.id;

                            //ajax request
                            const xhr = new XMLHttpRequest();
                            xhr.open('POST', 'delete_event.php', true);
                            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                            const data = 'event_id=' + info.event.id;

                            xhr.onload = function() {
                                if (xhr.status === 200) {
                                    console.log(xhr.responseText);
                                } else {
                                    console.error('Error:', xhr.status);
                                }
                            };

                            xhr.send(data);
                            location.reload();
                        }
                    });
                }
            });

            calendar.render();
        });

        // code to render the sidebar wala calendar
        var daycalendarEl = document.getElementById('daycalendar');
        var daycalendar = new FullCalendar.Calendar(daycalendarEl, {
            initialView: 'listDay',
            headerToolbar: {
                start: '',
                center: '',
                end: '',
            },
            selectable: true,
            events: <?php echo json_encode($events); ?>
        });

        daycalendar.render();

        // code to change the background of the calendar
        const bg = ['1.jpg', '2.jpg', '3.jpg', '4.jpg', '5.jpg', '6.jpg'];

        var random = -1;

        function changebg() {
            random++;
            if (random == bg.length) {
                random = 0;
            }
            document.getElementById("calendarbg").style.backgroundImage = "url('../../images/calendar/" + bg[random] + "')";
            document.getElementById("calendarbgday").style.backgroundImage = "url('../../images/calendar/" + bg[random] + "')";
        }
    </script>
</body>

</html>