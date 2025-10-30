(function () {
  function toggleTable() {
    var dateInput = document.getElementById("setDate");
    var classroomTable = document.getElementById("classroomTable");
    var classroomTableBody = document.getElementById("classroomTableBody");
    if (dateInput.value !== "") {
      classroomTable.style.display = "block";
      var selectedDate = dateInput.value;
      var xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
          let data = JSON.parse(xhr.responseText);
          let tableHTML =
            " <tr><th style='border:0'>Room Number</th><th style='border:0'>Date</th><th style='border:0'>Time</th><th style='border:0'>Seat Capacity</th><th style='width:20px;background-color:white;border:0'></th></tr>";
          let row = "";
          for (let i = 0; i < data.length; i++) {
            if (data[i].status === "booked") {
              row =
                "<tr id=" +
                i +
                ">" +
                "<td style='background-color: grey;' id='row1id" +
                i +
                "'>" +
                data[i].id +
                "</td>" +
                "<td style='background-color: grey;' id='row2date" +
                i +
                "'>" +
                data[i].date +
                "</td>" +
                "<td style='background-color: grey;' id='row3time" +
                i +
                "'>" +
                "<span id='row31" +
                i +
                "'>" +
                data[i].start_time +
                "</span>" +
                " to " +
                data[i].end_time +
                "</td>" +
                "<td style='background-color: grey;' id='row4seats" +
                i +
                "'>" +
                data[i].seat_capacity +
                "</td>" +
                "<td style='border: 0; width:auto'>" +
                "<input type='checkbox' name='' id='checkbox" +
                i +
                "'" +
                "disabled>" +
                "</td>";

              ("</tr>");
            } else {
              row =
                "<tr id=" +
                i +
                ">" +
                "<td id='row1id" +
                i +
                "'>" +
                data[i].id +
                "</td>" +
                "<td id='row2date" +
                i +
                "'>" +
                data[i].date +
                "</td>" +
                "<td id='row3time" +
                i +
                "'>" +
                "<span id='row31" +
                i +
                "'>" +
                data[i].start_time +
                "</span>" +
                " to " +
                data[i].end_time +
                "</td>" +
                "<td id='row4seats" +
                i +
                "'>" +
                data[i].seat_capacity +
                "</td>" +
                "<td style='border: 0; width:auto'>" +
                "<input type='checkbox' name='' id='checkbox" +
                i +
                "'>" +
                "</td>";
              ("</tr>");
            }

            tableHTML += row;
          }
          classroomTableBody.innerHTML = tableHTML;
        }
      };
      xhr.open("POST", "getTableData.php", true);
      xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xhr.setRequestHeader("X-CSRF-Token", getCsrf());
      xhr.send("date=" + selectedDate);
    } else {
      classroomTable.style.display = "none";
      classroomTableBody.innerHTML = "";
    }
  }

  function toggleHighlight(id) {
    var checkbox = document.getElementById("checkbox" + id);
    if (checkbox.checked) {
      document.getElementById("row1id" + id).classList.add("highlighted");
      document.getElementById("row2date" + id).classList.add("highlighted");
      document.getElementById("row3time" + id).classList.add("highlighted");
      document.getElementById("row4seats" + id).classList.add("highlighted");
    } else {
      document.getElementById("row1id" + id).classList.remove("highlighted");
      document.getElementById("row2date" + id).classList.remove("highlighted");
      document.getElementById("row3time" + id).classList.remove("highlighted");
      document.getElementById("row4seats" + id).classList.remove("highlighted");
    }
  }

  function submitForm() {
    var selectedRows = [];
    var classroomTableBody = document.getElementById("classroomTableBody");
    var rows = classroomTableBody.getElementsByTagName("tr");
    for (var i = 1; i < rows.length; i++) {
      var row = rows[i];
      var checkbox = row.getElementsByTagName("input")[0];
      if (checkbox.checked) {
        var roomNumber = row.getElementsByTagName("td")[0].innerHTML;
        var date = row.getElementsByTagName("td")[1].innerHTML;
        var start_time = row
          .getElementsByTagName("td")[2]
          .getElementsByTagName("span")[0].innerHTML;
        var seatCapacity = row.getElementsByTagName("td")[3].innerHTML;
        selectedRows.push({
          roomNumber: roomNumber,
          date: date,
          start_time: start_time,
          seatCapacity: seatCapacity,
        });
      }
    }

    if (selectedRows.length === 0) {
      alert("Please select at least one classroom to book.");
      return;
    }

    // Send an HTTP request to the server-side script
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
      if (xhr.readyState === XMLHttpRequest.DONE) {
        if (xhr.status === 200) {
         
          location.href = "useraccount.php";
          // Insertion successful, update the UI accordingly
        } else {
          console.error(xhr.statusText);
          // Insertion failed, show an error message
        }
      }
    };
    xhr.open("POST", "insertClassRoomBooking.php");
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.setRequestHeader("X-CSRF-Token", getCsrf());
    xhr.send(JSON.stringify(selectedRows));
  }

  function getCsrf() {
    var el = document.querySelector('meta[name="csrf-token"]');
    return el ? el.getAttribute("content") : "";
  }

  document.addEventListener("DOMContentLoaded", function () {
    const logo = document.getElementById("appLogo");

    if (logo) {
      logo.addEventListener("click", function () {
        window.location.href = "index.php";
      });
    }

    const table = document.getElementById("mainTable");

    table.addEventListener("click", (e) => {
      const btn = e.target.closest("tr");
      if (!btn) return;
      const row = btn;
      const match = row?.id.match(/^(\d+)$/);
      const i = match ? parseInt(match[1], 10) : NaN;
      toggleHighlight(i);
    });

    // cutoff hour (24h format)
    const CUTOFF_HOUR = 17; // 5 PM

    const now = new Date();

    // Decide which date should be the earliest selectable
    // If it's 17:00 or later: force them to book starting tomorrow
    // Otherwise: let them still pick today
    const minDateObj = new Date(
      now.getFullYear(),
      now.getMonth(),
      now.getDate() + (now.getHours() >= CUTOFF_HOUR ? 1 : 0)
    );

    // Format yyyy-mm-dd for <input type="date">
    const yyyy = minDateObj.getFullYear();
    const mm = String(minDateObj.getMonth() + 1).padStart(2, "0");
    const dd = String(minDateObj.getDate()).padStart(2, "0");
    const minDateStr = `${yyyy}-${mm}-${dd}`;

    // Apply it to the picker
    const dateInput = document.getElementById("setDate");
    if (dateInput) {
      dateInput.setAttribute("min", minDateStr);

      // Optional nice touch:
      // If the current value (or default value) is now "invalid" (e.g. page prefilled today but it's after 5 PM),
      // snap it forward to min so the UI doesn't look empty/blocked.
      if (dateInput.value && dateInput.value < minDateStr) {
        dateInput.value = minDateStr;
      }
    }

    document.getElementById("setDate").addEventListener("change", toggleTable);
    document.getElementById("submitBtn").addEventListener("click", submitForm);
  });
})();
