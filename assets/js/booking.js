/**
 * Desire Travel - Interactive Seat Reservation Engine
 */

document.addEventListener('DOMContentLoaded', () => {
  const seatBoxes = document.querySelectorAll('.seat-box.available');
  const selectedSeatsInput = document.getElementById('selected_seats_input');
  const seatCountInput = document.getElementById('seat_count_input');
  const totalFareInput = document.getElementById('total_fare_input');
  const seatDisplay = document.getElementById('selected_seats_display');
  const totalFareDisplay = document.getElementById('total_fare_display');
  const baseFarePerSeat = parseFloat(document.getElementById('base_fare_per_seat')?.value || 0);

  let selectedSeats = [];

  const updateBookingState = () => {
    // Update inputs
    if (selectedSeatsInput) selectedSeatsInput.value = selectedSeats.join(', ');
    if (seatCountInput) seatCountInput.value = selectedSeats.length;

    const totalFare = selectedSeats.length * baseFarePerSeat;
    if (totalFareInput) totalFareInput.value = totalFare.toFixed(2);

    // Update UI displays
    if (seatDisplay) {
      seatDisplay.textContent = selectedSeats.length > 0 
        ? selectedSeats.join(', ') + ` (${selectedSeats.length} Seats)`
        : 'None selected';
    }

    if (totalFareDisplay) {
      totalFareDisplay.textContent = '₹' + totalFare.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Enable/Disable Submit Button
    const bookBtn = document.getElementById('submit_booking_btn');
    if (bookBtn) {
      bookBtn.disabled = selectedSeats.length === 0;
    }
  };

  seatBoxes.forEach(seat => {
    seat.addEventListener('click', () => {
      const seatNo = seat.getAttribute('data-seat-no');

      if (seat.classList.contains('selected')) {
        seat.classList.remove('selected');
        selectedSeats = selectedSeats.filter(s => s !== seatNo);
      } else {
        // Max 6 seats per booking
        if (selectedSeats.length >= 6) {
          alert('Maximum 6 seats can be selected per single booking transaction.');
          return;
        }
        seat.classList.add('selected');
        selectedSeats.push(seatNo);
      }

      // Keep sorted
      selectedSeats.sort((a, b) => parseInt(a) - parseInt(b));
      updateBookingState();
    });
  });
});
