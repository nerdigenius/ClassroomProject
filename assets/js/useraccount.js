(function () {
  function popup() {
    document.getElementById("popupContainer").style.display = "flex";
  }

  function popupClose() {
    document.getElementById("popupContainer").style.display = "none";
  }
   function getCsrf() {
  var el = document.querySelector('meta[name="csrf-token"]');
  return el ? el.getAttribute('content') : '';
}

  function GoClassRoom() {
    location.href = "classRoomBookings.php";
  }

  function GoSeats() {
    location.href = "seatBookings.php";
  }

  function resetPassword() {
    location.href = "resetPassword.php";
  }

  function DeleteSeats(id) {
    // "innerText" is used instead of innerHTML to retrieve the text content, which prevents any injected HTML from being executed.

    var selectedRows = [];
    var classroomTable = document.querySelector("#SeatsTable");
    var row = classroomTable.querySelector("#row2" + id);
    var seat_number = row.getElementsByTagName("td")[0].innerText.trim();
    var roomNumber = row.getElementsByTagName("td")[1].innerText.trim();
    var date = row.getElementsByTagName("td")[2].innerText.trim();
    var start_time = row
      .getElementsByTagName("td")[3]
      .getElementsByTagName("span")[0]
      .innerText.trim();

    // var selectedRows = [];
    // var classroomTable = document.querySelector("#SeatsTable");
    // var row = classroomTable.querySelector("#row1" + id);
    // var seat_number = row.getElementsByTagName("td")[0].innerHTML;
    // var roomNumber = row.getElementsByTagName("td")[1].innerHTML;
    // var date = row.getElementsByTagName("td")[2].innerHTML;
    // var start_time = row.getElementsByTagName("td")[3].getElementsByTagName("span")[0].innerHTML;

    // Sanitized inputs  using encodeURIComponent to handle special characters
    seat_number = encodeURIComponent(seat_number);
    roomNumber = encodeURIComponent(roomNumber);
    date = encodeURIComponent(date);
    start_time = start_time;

    selectedRows.push({
      seat_number: seat_number,
      roomNumber: roomNumber,
      date: date,
      start_time: start_time,
    });

    console.log(selectedRows);

    // Send an HTTP request to the server-side script
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
      if (xhr.readyState === XMLHttpRequest.DONE) {
        if (xhr.status === 200) {
          var response = JSON.parse(xhr.responseText);
          console.log(response["success"]);
          // console.log("send success!!!")
          if (response["success"] === true) {
            row.remove();
          }
          //console.log(xhr.responseText)
          //location.href = 'useraccount.php'
          // Insertion successful, update the UI accordingly
        } else {
          console.error(xhr.statusText);
          console.log("send failed!!!");
          // Insertion failed, show an error message
        }
      }
    };
    xhr.open("POST", "DeleteSeats.php");
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.setRequestHeader("X-CSRF-Token", getCsrf());
    xhr.send(JSON.stringify(selectedRows));
  }

  function DeleteClassroom(id) {
    // "innerText" is used instead of innerHTML to retrieve the text content, which prevents any injected HTML from being executed.
    var selectedRows = [];
    var classroomTable = document.querySelector("#classRoomTable");
    var row = classroomTable.querySelector("#row1" + id);
    var roomNumber = row.getElementsByTagName("td")[0].innerText;
    var date = row.getElementsByTagName("td")[1].innerText;
    var start_time = row
      .getElementsByTagName("td")[2]
      .getElementsByTagName("span")[0].innerText;

    // Sanitized inputs  using encodeURIComponent to handle special characters
    roomNumber = encodeURIComponent(roomNumber);
    date = encodeURIComponent(date);
    start_time = start_time;

    selectedRows.push({
      roomNumber: roomNumber,
      date: date,
      start_time: start_time,
    });

    // var selectedRows = [];
    // var classroomTable = document.querySelector("#classRoomTable");
    // var row = classroomTable.querySelector("#row1" + id);
    // var roomNumber = row.getElementsByTagName("td")[0].innerHTML;
    // var date = row.getElementsByTagName("td")[1].innerHTML;
    // var start_time = row.getElementsByTagName("td")[2].getElementsByTagName("span")[0].innerHTML;
    // selectedRows.push({
    //     roomNumber: roomNumber,
    //     date: date,
    //     start_time: start_time,
    // });

    console.log(selectedRows);

    // Send an HTTP request to the server-side script
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
      if (xhr.readyState === XMLHttpRequest.DONE) {
        if (xhr.status === 200) {
          var response = JSON.parse(xhr.responseText);
          console.log(response["success"]);
          console.log("send success!!!");
          if (response["success"] === true) {
            row.remove();
          }
          //location.href = 'useraccount.php'
          // Insertion successful, update the UI accordingly
        } else {
          console.error(xhr.statusText);
          console.log("send failed!!!");
          // Insertion failed, show an error message
        }
      }
    };
    xhr.open("POST", "DeleteClassRoom.php");
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.setRequestHeader("X-CSRF-Token", getCsrf());
    xhr.send(JSON.stringify(selectedRows));
    console.log(selectedRows);
  }
  document.addEventListener("DOMContentLoaded", () => {
    const table = document.getElementById("classRoomTable");
    const table2 = document.getElementById("SeatsTable");

    table.addEventListener("click", (e) => {
      const btn = e.target.closest(".delete-classroom-btn");
      if (!btn) return;
      const row = btn.closest("tr");
      const match = row?.id.match(/^row1(\d+)$/);
      const i = match ? parseInt(match[1], 10) : NaN;
      if (Number.isFinite(i)) DeleteClassroom(i);
    });

     table2.addEventListener("click", (e) => {
      const btn = e.target.closest(".delete-seat-btn");
      if (!btn) return;
      const row = btn.closest("tr");
      const match = row?.id.match(/^row2(\d+)$/);
      const i = match ? parseInt(match[1], 10) : NaN;
      if (Number.isFinite(i)) DeleteSeats(i);
    });

    document.getElementById("reset_password").addEventListener("click", (e) => {
      e.preventDefault();
      resetPassword();
    });
    document.getElementById("popup").addEventListener("click", (e) => {
      e.preventDefault();
      popup();
    });
    document.getElementById("popupContainer").addEventListener("click", (e) => {
      e.preventDefault();
      popupClose();
    });
    document.getElementById("ClassRoomBtn").addEventListener("click", (e) => {
      e.preventDefault();
      GoClassRoom();
    });
    document.getElementById("SeatsBtn").addEventListener("click", (e) => {
      e.preventDefault();
      GoSeats();
    });
  });
})();
