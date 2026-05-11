import { useEffect, useMemo, useState } from "react";
import { X, Minus, Plus, CreditCard } from "lucide-react";

const API_BASE_URL = import.meta.env.VITE_API_URL || "http://localhost:8000";

const TicketBookingModal = ({ event, onClose, onConfirm }) => {
  const show = event.show;
  const bookingMode = show?.booking_mode || "reserved_seating";
  const ticketTypes = show?.ticket_types || [];

  const [seats, setSeats] = useState([]);
  const [selectedSeatIds, setSelectedSeatIds] = useState([]);
  const [selectedTicketTypeId, setSelectedTicketTypeId] = useState(
    ticketTypes[0]?.id || "",
  );
  const [quantity, setQuantity] = useState(1);
  const [loadingSeats, setLoadingSeats] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState("");

  useEffect(() => {
    document.body.style.overflow = "hidden";
    return () => {
      document.body.style.overflow = "unset";
    };
  }, []);

  useEffect(() => {
    if (bookingMode !== "reserved_seating" || !show?.id) return;

    const fetchSeats = async () => {
      setLoadingSeats(true);
      setError("");

      try {
        const response = await fetch(`${API_BASE_URL}/api/shows/${show.id}/seats`, {
          headers: { Accept: "application/json" },
        });

        if (!response.ok) throw new Error("Failed to load seat map");

        const result = await response.json();
        setSeats(Array.isArray(result.data) ? result.data : []);
      } catch (fetchError) {
        setError(fetchError.message || "Could not load seats");
      } finally {
        setLoadingSeats(false);
      }
    };

    fetchSeats();
  }, [bookingMode, show?.id]);

  const groupedSeats = useMemo(() => {
    return seats.reduce((groups, seat) => {
      groups[seat.row_label] ||= [];
      groups[seat.row_label].push(seat);
      return groups;
    }, {});
  }, [seats]);

  const selectedTicketType = ticketTypes.find(
    (ticketType) => Number(ticketType.id) === Number(selectedTicketTypeId),
  );

  const total = useMemo(() => {
    if (bookingMode === "reserved_seating") {
      return seats
        .filter((seat) => selectedSeatIds.includes(seat.id))
        .reduce((sum, seat) => sum + Number(seat.price || 0), 0);
    }

    if (bookingMode === "tiered_tickets") {
      return Number(selectedTicketType?.price || 0) * quantity;
    }

    return Number(show?.price || 0) * quantity;
  }, [bookingMode, quantity, seats, selectedSeatIds, selectedTicketType, show]);

  const toggleSeat = (seat) => {
    if (seat.status !== "available") return;

    setSelectedSeatIds((current) =>
      current.includes(seat.id)
        ? current.filter((seatId) => seatId !== seat.id)
        : [...current, seat.id],
    );
  };

  const submitBooking = async () => {
    setError("");

    if (bookingMode === "reserved_seating" && selectedSeatIds.length === 0) {
      setError("Please select at least one seat.");
      return;
    }

    if (bookingMode === "tiered_tickets" && !selectedTicketTypeId) {
      setError("Please select a ticket type.");
      return;
    }

    setSubmitting(true);

    const payload = {
      show_id: show.id,
      gateway: "manual",
    };

    if (bookingMode === "reserved_seating") {
      payload.seat_ids = selectedSeatIds;
    } else {
      payload.quantity = quantity;
      if (bookingMode === "tiered_tickets") {
        payload.ticket_type_id = selectedTicketTypeId;
      }
    }

    try {
      const response = await fetch(`${API_BASE_URL}/api/bookings`, {
        method: "POST",
        credentials: "include",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify(payload),
      });

      const result = await response.json().catch(() => ({}));

      if (!response.ok) {
        throw new Error(
          result?.message ||
            Object.values(result?.errors || {})?.flat()?.[0] ||
            "Booking failed. Please sign in and try again.",
        );
      }

      onConfirm?.(result);
      alert(`Booking confirmed for ${event.title}!`);
      onClose();
    } catch (submitError) {
      setError(submitError.message || "Booking failed.");
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
      <div className="bg-white rounded-3xl max-w-6xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
        <div className="sticky top-0 bg-white border-b border-gray-100 px-6 py-4 flex justify-between items-center rounded-t-3xl z-10">
          <div>
            <h2 className="text-xl font-bold text-gray-900">Book Tickets</h2>
            <p className="text-xs text-gray-500 mt-1">{event.title}</p>
          </div>
          <button onClick={onClose} className="p-2 rounded-full hover:bg-gray-100">
            <X size={20} />
          </button>
        </div>

        <div className="p-6 space-y-6">
          <div className="rounded-2xl bg-gray-50 p-4">
            <p className="text-sm font-semibold text-gray-900">{event.date}</p>
            <p className="text-sm text-gray-500 mt-1">{event.location}</p>
          </div>

          {bookingMode === "reserved_seating" && (
            <div>
              <div className="mb-4 rounded-2xl bg-gray-900 px-6 py-3 text-center text-sm font-semibold text-white">
                STAGE
              </div>

              {loadingSeats && (
                <p className="text-sm text-gray-500 font-medium">Loading seat map...</p>
              )}

              {!loadingSeats && (
                <div className="space-y-3 overflow-x-auto rounded-2xl border border-gray-100 p-4">
                  {Object.entries(groupedSeats).map(([row, rowSeats]) => (
                    <div key={row} className="flex items-center gap-3">
                      <span className="w-8 text-sm font-semibold text-gray-500">
                        {row}
                      </span>
                      <div className="flex flex-wrap gap-2">
                        {rowSeats.map((seat) => {
                          const selected = selectedSeatIds.includes(seat.id);
                          const disabled = seat.status !== "available";

                          return (
                            <button
                              key={seat.id}
                              type="button"
                              disabled={disabled}
                              onClick={() => toggleSeat(seat)}
                              title={`${seat.seat_number} - ${seat.status}`}
                              className={`h-10 w-10 rounded-xl text-xs font-bold text-white transition ${
                                selected
                                  ? "bg-orange-500"
                                  : disabled
                                    ? seat.status === "booked"
                                      ? "bg-rose-500 cursor-not-allowed"
                                      : "bg-slate-400 cursor-not-allowed"
                                    : "bg-emerald-500 hover:bg-emerald-600"
                              }`}
                            >
                              {seat.column_number}
                            </button>
                          );
                        })}
                      </div>
                    </div>
                  ))}
                </div>
              )}

              <div className="mt-4 flex flex-wrap gap-3 text-xs font-semibold">
                <span className="rounded-full bg-emerald-100 px-3 py-1 text-emerald-700">Available</span>
                <span className="rounded-full bg-orange-100 px-3 py-1 text-orange-700">Selected</span>
                <span className="rounded-full bg-rose-100 px-3 py-1 text-rose-700">Booked</span>
                <span className="rounded-full bg-slate-100 px-3 py-1 text-slate-700">Blocked</span>
              </div>
            </div>
          )}

          {bookingMode === "tiered_tickets" && (
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Select Ticket Type
              </label>
              <div className="space-y-2">
                {ticketTypes.map((ticketType) => (
                  <label
                    key={ticketType.id}
                    className={`flex items-center justify-between p-3 rounded-xl border cursor-pointer transition ${
                      Number(selectedTicketTypeId) === Number(ticketType.id)
                        ? "border-orange-500 bg-orange-50"
                        : "border-gray-200 hover:border-orange-200"
                    }`}
                  >
                    <div className="flex items-center gap-3">
                      <input
                        type="radio"
                        name="ticketType"
                        value={ticketType.id}
                        checked={Number(selectedTicketTypeId) === Number(ticketType.id)}
                        onChange={() => setSelectedTicketTypeId(ticketType.id)}
                        className="w-4 h-4 text-orange-500 focus:ring-orange-500"
                      />
                      <span>
                        <span className="block font-medium text-gray-800">
                          {ticketType.name}
                        </span>
                        {ticketType.description && (
                          <span className="block text-xs text-gray-500">
                            {ticketType.description}
                          </span>
                        )}
                      </span>
                    </div>
                    <span className="font-bold text-gray-900">
                      {ticketType.formatted_price}
                    </span>
                  </label>
                ))}
              </div>
            </div>
          )}

          {bookingMode !== "reserved_seating" && (
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Quantity
              </label>
              <div className="flex items-center gap-4">
                <button
                  type="button"
                  onClick={() => setQuantity((prev) => Math.max(prev - 1, 1))}
                  className="p-2 rounded-full border border-gray-200 hover:bg-gray-50"
                >
                  <Minus size={16} />
                </button>
                <span className="text-lg font-semibold w-8 text-center">
                  {quantity}
                </span>
                <button
                  type="button"
                  onClick={() => setQuantity((prev) => prev + 1)}
                  className="p-2 rounded-full border border-gray-200 hover:bg-gray-50"
                >
                  <Plus size={16} />
                </button>
              </div>
            </div>
          )}

          {error && (
            <div className="rounded-2xl bg-red-50 p-4 text-sm font-medium text-red-600">
              {error}
            </div>
          )}

          <div className="bg-gray-50 p-4 rounded-2xl flex justify-between items-center">
            <span className="font-semibold text-gray-800">Total Amount</span>
            <span className="text-2xl font-black text-orange-600">
              {new Intl.NumberFormat("en-IN", {
                style: "currency",
                currency: show?.currency_code || "INR",
                maximumFractionDigits: 0,
              }).format(total)}
            </span>
          </div>

          <button
            type="button"
            disabled={submitting}
            onClick={submitBooking}
            className="w-full bg-orange-500 hover:bg-orange-600 disabled:opacity-60 text-white font-bold py-3 rounded-xl transition shadow-lg flex items-center justify-center gap-2"
          >
            <CreditCard size={18} />
            {submitting ? "Booking..." : "Confirm Booking"}
          </button>
        </div>
      </div>
    </div>
  );
};

export default TicketBookingModal;
