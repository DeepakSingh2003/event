import { useEffect, useMemo, useState } from "react";
import { Download, History, TicketCheck } from "lucide-react";
import { Link } from "react-router-dom";

const API_BASE_URL = import.meta.env.VITE_API_URL || "http://localhost:8000";

const formatMoney = (amount, currency = "INR") =>
  new Intl.NumberFormat("en-IN", {
    style: "currency",
    currency,
    maximumFractionDigits: 0,
  }).format(Number(amount || 0));

const formatDateTime = (value) => {
  if (!value) return "Date pending";

  return new Date(value).toLocaleString("en-IN", {
    day: "2-digit",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  });
};

const formatShowDate = (show) => {
  if (!show?.show_date) return "Show time pending";

  return new Date(`${show.show_date}T${show.show_time || "00:00"}`).toLocaleString(
    "en-IN",
    {
      weekday: "short",
      day: "2-digit",
      month: "short",
      hour: "2-digit",
      minute: "2-digit",
      hour12: true,
    },
  );
};

const normalizeBookings = (payload) => {
  if (Array.isArray(payload?.data)) return payload.data;
  if (Array.isArray(payload?.bookings)) return payload.bookings;
  if (Array.isArray(payload)) return payload;
  return [];
};

const MyBookingsPage = () => {
  const [bookings, setBookings] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [downloadingTicketId, setDownloadingTicketId] = useState(null);

  useEffect(() => {
    const fetchBookings = async () => {
      setLoading(true);
      setError("");

      try {
        const response = await fetch(`${API_BASE_URL}/api/bookings`, {
          credentials: "include",
          headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
          },
        });

        const result = await response.json().catch(() => ({}));

        if (response.status === 401) {
          setBookings([]);
          setError("Please sign in to view your booking history and tickets.");
          return;
        }

        if (!response.ok) {
          throw new Error(result?.message || "Could not load your bookings.");
        }

        setBookings(normalizeBookings(result));
      } catch (fetchError) {
        setError(fetchError.message || "Could not load your bookings.");
      } finally {
        setLoading(false);
      }
    };

    fetchBookings();
  }, []);

  const tickets = useMemo(
    () => bookings.filter((booking) => booking.status !== "cancelled"),
    [bookings],
  );

  const downloadTicket = async (booking) => {
    if (!booking.ticket_url || downloadingTicketId) return;

    setDownloadingTicketId(booking.id);
    setError("");

    try {
      const response = await fetch(booking.ticket_url, {
        credentials: "include",
        headers: {
          Accept: "application/pdf",
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      if (!response.ok) {
        throw new Error("Ticket PDF is not ready yet.");
      }

      const ticketBlob = await response.blob();
      const ticketUrl = window.URL.createObjectURL(ticketBlob);
      const downloadLink = document.createElement("a");
      downloadLink.href = ticketUrl;
      downloadLink.download = `${booking.booking_reference || "ticket"}.pdf`;
      document.body.appendChild(downloadLink);
      downloadLink.click();
      downloadLink.remove();
      window.URL.revokeObjectURL(ticketUrl);
    } catch (downloadError) {
      setError(downloadError.message || "Could not download ticket.");
    } finally {
      setDownloadingTicketId(null);
    }
  };

  return (
    <main className="min-h-screen bg-white mt-20 text-gray-900">
      <section className="bg-[#111827] px-4 py-16 text-white md:py-24">
        <div className="mx-auto max-w-7xl">
          <p className="text-sm font-bold uppercase tracking-[0.24em] text-orange-300">
            Account
          </p>
          <h1 className="mt-5 text-4xl font-black md:text-6xl">
            Booking History
          </h1>
          <p className="mt-5 max-w-2xl text-base leading-7 text-white/70 md:text-lg">
            View your bookings, open your ticket, and download the confirmed
            ticket when it is ready.
          </p>
        </div>
      </section>

      <section className="px-4 py-12 md:py-16">
        <div className="mx-auto max-w-7xl">
          {loading ? (
            <div className="border-y border-gray-200 py-14 text-center">
              <p className="font-semibold text-gray-500">Loading bookings...</p>
            </div>
          ) : error ? (
            <div className="border-y border-gray-200 py-14 text-center">
              <TicketCheck size={42} className="mx-auto text-[#ff6b00]" />
              <h2 className="mt-5 text-3xl font-black">Tickets unavailable</h2>
              <p className="mx-auto mt-4 max-w-xl leading-7 text-gray-600">
                {error}
              </p>
            </div>
          ) : bookings.length === 0 ? (
            <div className="border-y border-gray-200 py-14 text-center">
              <History size={42} className="mx-auto text-[#ff6b00]" />
              <h2 className="mt-5 text-3xl font-black">No bookings yet</h2>
              <p className="mx-auto mt-4 max-w-xl leading-7 text-gray-600">
                Your completed ticket purchases will appear here after checkout.
              </p>
              <Link
                to="/search"
                className="mt-8 inline-flex rounded-full bg-[#ff6b00] px-6 py-3 text-sm font-bold text-white"
              >
                Find Events
              </Link>
            </div>
          ) : (
            <div className="grid gap-8 lg:grid-cols-[1.15fr_0.85fr]">
              <div>
                <div className="mb-5 flex items-center gap-3">
                  <History className="text-[#ff6b00]" size={22} />
                  <h2 className="text-2xl font-black">Booking History</h2>
                </div>

                <div className="space-y-4">
                  {bookings.map((booking) => (
                    <article
                      key={booking.id}
                      className="rounded-lg border border-gray-200 p-5 shadow-sm"
                    >
                      <div className="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div>
                          <p className="text-xs font-bold uppercase tracking-[0.2em] text-[#ff6b00]">
                            {booking.booking_reference}
                          </p>
                          <h3 className="mt-2 text-xl font-black">
                            {booking.event?.title || booking.show?.event?.title || "Event"}
                          </h3>
                          <p className="mt-2 text-sm font-medium text-gray-500">
                            {formatShowDate(booking.show)}
                          </p>
                          <p className="mt-1 text-sm text-gray-500">
                            {booking.show?.venue?.name || "Venue pending"}
                          </p>
                        </div>
                        <div className="text-left md:text-right">
                          <span className="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-bold capitalize text-gray-700">
                            {booking.status}
                          </span>
                          <p className="mt-3 text-lg font-black">
                            {formatMoney(
                              booking.total_amount,
                              booking.show?.currency_code || "INR",
                            )}
                          </p>
                          <p className="mt-1 text-xs text-gray-400">
                            Booked {formatDateTime(booking.booked_at)}
                          </p>
                          {booking.ticket_url ? (
                            <button
                              type="button"
                              onClick={() => downloadTicket(booking)}
                              disabled={downloadingTicketId === booking.id}
                              className="mt-4 inline-flex items-center justify-center gap-2 rounded-full bg-[#ff6b00] px-4 py-2 text-xs font-bold text-white"
                            >
                              <Download size={14} />
                              {downloadingTicketId === booking.id
                                ? "Downloading..."
                                : "Download Ticket"}
                            </button>
                          ) : (
                            <button
                              type="button"
                              disabled
                              className="mt-4 inline-flex items-center justify-center gap-2 rounded-full bg-gray-200 px-4 py-2 text-xs font-bold text-gray-500"
                            >
                              <Download size={14} />
                              Ticket Pending
                            </button>
                          )}
                        </div>
                      </div>
                    </article>
                  ))}
                </div>
              </div>

              <aside id="tickets">
                <div className="mb-5 flex items-center gap-3">
                  <TicketCheck className="text-[#ff6b00]" size={22} />
                  <h2 className="text-2xl font-black">Your Ticket</h2>
                </div>

                <div className="space-y-4">
                  {tickets.map((booking) => (
                    <article
                      key={booking.id}
                      className="rounded-lg border border-gray-200 bg-[#f7f8fb] p-5"
                    >
                      <p className="text-xs font-bold uppercase tracking-[0.2em] text-gray-400">
                        {booking.booking_reference}
                      </p>
                      <h3 className="mt-2 text-lg font-black">
                        {booking.event?.title || booking.show?.event?.title || "Event"}
                      </h3>
                      <p className="mt-2 text-sm text-gray-500">
                        {formatShowDate(booking.show)}
                      </p>
                      {booking.ticket_url ? (
                        <button
                          type="button"
                          onClick={() => downloadTicket(booking)}
                          disabled={downloadingTicketId === booking.id}
                          className="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-full bg-[#ff6b00] px-5 py-3 text-sm font-bold text-white"
                        >
                          <Download size={16} />
                          {downloadingTicketId === booking.id
                            ? "Downloading..."
                            : "Download Ticket"}
                        </button>
                      ) : (
                        <button
                          type="button"
                          disabled
                          className="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-full bg-gray-200 px-5 py-3 text-sm font-bold text-gray-500"
                        >
                          <Download size={16} />
                          Ticket Pending
                        </button>
                      )}
                    </article>
                  ))}
                </div>
              </aside>
            </div>
          )}
        </div>
      </section>
    </main>
  );
};

export default MyBookingsPage;
