import { useEffect, useMemo, useRef, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { ChevronLeft, CreditCard, Minus, Plus, X } from "lucide-react";

const API_BASE_URL = import.meta.env.VITE_API_URL || "http://localhost:8000";

const getCookie = (name) => {
  const match = document.cookie
    .split("; ")
    .find((cookie) => cookie.startsWith(`${name}=`));

  return match ? decodeURIComponent(match.split("=").slice(1).join("=")) : "";
};

const ensureCsrfCookie = async () => {
  await fetch(`${API_BASE_URL}/sanctum/csrf-cookie`, {
    credentials: "include",
    headers: { Accept: "application/json" },
  });
};

const apiRequest = async (path, options = {}) => {
  const method = options.method || "GET";
  const isFormData = options.body instanceof FormData;
  const headers = {
    Accept: "application/json",
    ...(options.body && !isFormData ? { "Content-Type": "application/json" } : {}),
    ...(options.headers || {}),
  };

  if (method !== "GET") {
    await ensureCsrfCookie();
    headers["X-XSRF-TOKEN"] = getCookie("XSRF-TOKEN");
  }

  return fetch(`${API_BASE_URL}${path}`, {
    credentials: "include",
    ...options,
    method,
    headers,
  });
};

const loadScript = (src) =>
  new Promise((resolve, reject) => {
    const existingScript = document.querySelector(`script[src="${src}"]`);

    if (existingScript) {
      resolve();
      return;
    }

    const script = document.createElement("script");
    script.src = src;
    script.onload = resolve;
    script.onerror = reject;
    document.body.appendChild(script);
  });

const formatMoney = (amount, currency = "INR") =>
  new Intl.NumberFormat("en-IN", {
    style: "currency",
    currency,
    maximumFractionDigits: 0,
  }).format(Number(amount || 0));

const formatDate = (show) => {
  if (!show?.show_date) return "Date coming soon";

  const date = new Date(`${show.show_date}T${show.show_time || "00:00"}`);

  return date.toLocaleString("en-IN", {
    weekday: "short",
    day: "2-digit",
    month: "short",
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  });
};

const BookingPage = () => {
  const { showId } = useParams();
  const navigate = useNavigate();
  const stripeRef = useRef(null);
  const stripeElementsRef = useRef(null);
  const stripeCardRef = useRef(null);
  const stripeCardContainerRef = useRef(null);

  const [show, setShow] = useState(null);
  const [seats, setSeats] = useState([]);
  const [selectedSeatIds, setSelectedSeatIds] = useState([]);
  const [selectedTicketTypeId, setSelectedTicketTypeId] = useState("");
  const [quantity, setQuantity] = useState(1);
  const [paymentProof, setPaymentProof] = useState(null);
  const [paymentConfig, setPaymentConfig] = useState({
    gateway: "manual",
    gateways: ["manual"],
  });
  const [selectedGateway, setSelectedGateway] = useState("manual");
  const [paymentOpen, setPaymentOpen] = useState(false);
  const [paymentReady, setPaymentReady] = useState(true);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState("");

  useEffect(() => {
    const fetchBookingData = async () => {
      setLoading(true);
      setError("");

      try {
        const [showResponse, paymentConfigResponse] = await Promise.all([
          apiRequest(`/api/shows/${showId}`),
          apiRequest("/api/payment-config"),
        ]);

        if (!showResponse.ok) throw new Error("Failed to load show");

        const showResult = await showResponse.json();
        const paymentConfigResult = await paymentConfigResponse.json().catch(() => ({}));
        const showData = showResult.data;
        setShow(showData);
        const config = paymentConfigResult.data || {
          gateway: "manual",
          gateways: ["manual"],
        };
        setPaymentConfig(config);
        setSelectedGateway(config.gateways?.includes(config.gateway) ? config.gateway : config.gateways?.[0] || "manual");
        setSelectedTicketTypeId(showData.ticket_types?.[0]?.id || "");

        if ((showData.booking_mode || "reserved_seating") === "reserved_seating") {
          const seatsResponse = await apiRequest(`/api/shows/${showId}/seats`);

          if (!seatsResponse.ok) throw new Error("Failed to load seat map");

          const seatsResult = await seatsResponse.json();
          setSeats(Array.isArray(seatsResult.data) ? seatsResult.data : []);
        }
      } catch (fetchError) {
        setError(fetchError.message || "Something went wrong");
      } finally {
        setLoading(false);
      }
    };

    fetchBookingData();
  }, [showId]);

  useEffect(() => {
    let mounted = true;

    const setupStripe = async () => {
      if (!paymentOpen || selectedGateway !== "stripe") {
        if (stripeCardRef.current) {
          stripeCardRef.current.destroy();
          stripeCardRef.current = null;
          stripeElementsRef.current = null;
        }

        setPaymentReady(true);
        return;
      }

      if (!paymentConfig.stripe_key) {
        setPaymentReady(false);
        setError("Stripe publishable key is not configured in admin settings.");
        return;
      }

      if (!stripeCardContainerRef.current || stripeCardRef.current) return;

      setPaymentReady(false);

      try {
        await loadScript("https://js.stripe.com/v3/");
        if (!mounted) return;

        stripeRef.current = window.Stripe(paymentConfig.stripe_key);
        stripeElementsRef.current = stripeRef.current.elements();
        stripeCardRef.current = stripeElementsRef.current.create("card", {
          style: {
            base: {
              color: "#111827",
              fontFamily: "Inter, system-ui, sans-serif",
              fontSize: "15px",
              "::placeholder": { color: "#9ca3af" },
            },
          },
        });
        stripeCardRef.current.mount(stripeCardContainerRef.current);
        setPaymentReady(true);
      } catch {
        setPaymentReady(false);
        setError("Stripe could not be loaded. Please try again.");
      }
    };

    setupStripe();

    return () => {
      mounted = false;
    };
  }, [paymentConfig, paymentOpen, selectedGateway]);

  const bookingMode = show?.booking_mode || "reserved_seating";
  const currency = show?.currency_code || "INR";
  const selectedTicketType = show?.ticket_types?.find(
    (ticketType) => Number(ticketType.id) === Number(selectedTicketTypeId),
  );

  const seatSections = useMemo(() => {
    const groups = {};

    seats.forEach((seat) => {
      const key = `${seat.seat_type || "Normal"}-${seat.price}`;
      groups[key] ||= {
        label: seat.seat_type || "Normal",
        price: seat.price,
        rows: {},
      };
      groups[key].rows[seat.row_label] ||= [];
      groups[key].rows[seat.row_label].push(seat);
    });

    return Object.values(groups);
  }, [seats]);

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

  const selectedCount =
    bookingMode === "reserved_seating" ? selectedSeatIds.length : quantity;

  const toggleSeat = (seat) => {
    if (seat.status !== "available") return;

    setSelectedSeatIds((current) =>
      current.includes(seat.id)
        ? current.filter((seatId) => seatId !== seat.id)
        : [...current, seat.id],
    );
  };

  const openPaymentStep = () => {
    setError("");

    if (bookingMode === "reserved_seating" && selectedSeatIds.length === 0) {
      setError("Please select at least one seat.");
      return;
    }

    if (bookingMode === "tiered_tickets" && !selectedTicketTypeId) {
      setError("Please select a ticket type.");
      return;
    }

    setPaymentOpen(true);
  };

  const submitBooking = async () => {
    setError("");

    if (selectedGateway === "stripe" && !paymentReady) {
      setError("Payment form is still loading. Please wait.");
      return;
    }

    if (selectedGateway === "manual" && !paymentProof) {
      setError("Please upload your payment screenshot.");
      return;
    }

    const payload =
      selectedGateway === "manual"
        ? new FormData()
        : {
            show_id: show.id,
            gateway: selectedGateway,
          };

    if (payload instanceof FormData) {
      payload.append("show_id", show.id);
      payload.append("gateway", selectedGateway);
      payload.append("payment_proof", paymentProof);
    }

    if (bookingMode === "reserved_seating") {
      if (payload instanceof FormData) {
        selectedSeatIds.forEach((seatId) => payload.append("seat_ids[]", seatId));
      } else {
        payload.seat_ids = selectedSeatIds;
      }
    } else {
      if (payload instanceof FormData) {
        payload.append("quantity", quantity);
      } else {
        payload.quantity = quantity;
      }

      if (bookingMode === "tiered_tickets") {
        if (payload instanceof FormData) {
          payload.append("ticket_type_id", selectedTicketTypeId);
        } else {
          payload.ticket_type_id = selectedTicketTypeId;
        }
      }
    }

    setSubmitting(true);

    try {
      const response = await apiRequest("/api/bookings", {
        method: "POST",
        body: payload instanceof FormData ? payload : JSON.stringify(payload),
      });

      const result = await response.json().catch(() => ({}));

      if (!response.ok) {
        throw new Error(
          result?.message ||
            Object.values(result?.errors || {})?.flat()?.[0] ||
            "Booking failed. Please sign in and try again.",
        );
      }

      await handlePayment(result);
    } catch (submitError) {
      setError(submitError.message || "Booking failed.");
      setSubmitting(false);
    }
  };

  const confirmBooking = async (booking, paymentId, paymentStatus = "paid") => {
    const bookingData = booking?.data || booking;
    const response = await apiRequest(`/api/bookings/${bookingData.id}/confirm`, {
      method: "POST",
      body: JSON.stringify({
        payment_id: paymentId,
        payment_status: paymentStatus,
      }),
    });

    const result = await response.json().catch(() => ({}));

    if (!response.ok) {
      throw new Error(
        result?.message ||
          Object.values(result?.errors || {})?.flat()?.[0] ||
          "Payment succeeded, but booking confirmation failed.",
      );
    }

    alert("Booking confirmed successfully.");
    setPaymentOpen(false);
    navigate(`/event/${show.event?.slug || show.event?.id}`);
  };

  const handlePayment = async (result) => {
    const booking = result.booking?.data || result.booking;
    const payment = result.payment || {};

    if (payment.gateway === "manual") {
      setPaymentOpen(false);
      alert("Payment proof uploaded. Your booking is pending admin confirmation.");
      navigate(`/event/${show.event?.slug || show.event?.id}`);
      return;
    }

    if (payment.gateway === "razorpay") {
      await openRazorpayCheckout(booking, payment);
      return;
    }

    if (payment.gateway === "stripe") {
      await confirmStripePayment(booking, payment);
      return;
    }

    throw new Error("Unsupported payment gateway.");
  };

  const openRazorpayCheckout = async (booking, payment) => {
    if (!paymentConfig.razorpay_key) {
      throw new Error("Razorpay key is not configured in admin settings.");
    }

    await loadScript("https://checkout.razorpay.com/v1/checkout.js");

    return new Promise((resolve, reject) => {
      const checkout = new window.Razorpay({
        key: paymentConfig.razorpay_key,
        amount: payment.payload?.amount,
        currency: payment.payload?.currency || paymentConfig.currency || currency,
        name: eventTitle,
        description: venueText,
        order_id: payment.payment_reference,
        handler: async (razorpayResponse) => {
          try {
            await confirmBooking(
              booking,
              razorpayResponse.razorpay_payment_id,
              "paid",
            );
            resolve();
          } catch (confirmationError) {
            reject(confirmationError);
          }
        },
        modal: {
          ondismiss: () => reject(new Error("Payment was cancelled.")),
        },
      });

      checkout.open();
    });
  };

  const confirmStripePayment = async (booking, payment) => {
    if (!stripeRef.current || !stripeCardRef.current || !payment.client_secret) {
      throw new Error("Stripe payment form is not ready.");
    }

    const { error: stripeError, paymentIntent } =
      await stripeRef.current.confirmCardPayment(payment.client_secret, {
        payment_method: {
          card: stripeCardRef.current,
        },
      });

    if (stripeError) {
      throw new Error(stripeError.message || "Stripe payment failed.");
    }

    await confirmBooking(
      booking,
      paymentIntent?.id || payment.payment_reference,
      paymentIntent?.status === "succeeded" ? "paid" : "pending",
    );
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-white pt-28 flex items-center justify-center">
        <p className="text-gray-500 font-medium">Loading booking page...</p>
      </div>
    );
  }

  if (error && !show) {
    return (
      <div className="min-h-screen bg-white pt-28 flex items-center justify-center">
        <div className="text-center">
          <p className="text-red-600 font-semibold">{error}</p>
          <button
            onClick={() => navigate(-1)}
            className="mt-4 text-orange-500 font-bold"
          >
            Go Back
          </button>
        </div>
      </div>
    );
  }

  const eventTitle = show?.event?.title || "Event";
  const venueText = show?.venue?.name
    ? `${show.venue.name}, ${show.venue.city || ""}`
    : show?.venue?.city || "Venue coming soon";
  const layoutLabel = show?.venue?.layout_label || "SCREEN";
  const layoutLabelPosition = show?.venue?.layout_label_position || "bottom";
  const showLayoutLabel = layoutLabelPosition !== "hidden" && layoutLabel;
  const gatewayLabels = {
    manual: "QR / Screenshot",
    razorpay: "Razorpay",
    stripe: "Card",
  };
  const enabledGateways = paymentConfig.gateways?.length
    ? paymentConfig.gateways
    : [paymentConfig.gateway || "manual"];
  const paymentPanel = (
    <>
      <div className="mt-6 rounded-xl border border-gray-200 p-4">
        <p className="text-sm font-bold text-gray-900">Payment Method</p>
        <div className="mt-3 grid gap-2 sm:grid-cols-3">
          {enabledGateways.map((gateway) => (
            <button
              key={gateway}
              type="button"
              onClick={() => setSelectedGateway(gateway)}
              className={`rounded-xl border px-3 py-3 text-sm font-bold transition ${
                selectedGateway === gateway
                  ? "border-orange-500 bg-orange-50 text-orange-700"
                  : "border-gray-200 bg-white text-gray-700"
              }`}
            >
              {gatewayLabels[gateway] || gateway}
            </button>
          ))}
        </div>
      </div>

      {selectedGateway === "manual" && (
        <div className="mt-6 rounded-xl border border-gray-200 p-4">
          <p className="text-sm font-bold text-gray-900">Manual QR Payment</p>
          {paymentConfig.manual_qr_url ? (
            <img
              src={paymentConfig.manual_qr_url}
              alt="Payment QR"
              className="mx-auto mt-4 max-h-72 w-full rounded-xl object-contain"
            />
          ) : (
            <p className="mt-3 rounded-xl bg-red-50 p-3 text-sm font-semibold text-red-600">
              QR image is not configured in admin settings.
            </p>
          )}
          <p className="mt-3 text-sm text-gray-500">
            {paymentConfig.manual_instructions ||
              "Scan the QR, pay the total amount, then upload your payment screenshot."}
          </p>
          <input
            type="file"
            accept="image/*"
            onChange={(event) => setPaymentProof(event.target.files?.[0] || null)}
            className="mt-4 block w-full rounded-xl border border-gray-200 p-3 text-sm"
          />
        </div>
      )}

      {selectedGateway === "stripe" && (
        <div className="mt-6 rounded-xl border border-gray-200 p-4">
          <p className="mb-3 text-sm font-bold text-gray-900">Card Payment</p>
          <div ref={stripeCardContainerRef} className="rounded-lg border border-gray-200 bg-white p-3" />
        </div>
      )}

      {selectedGateway === "razorpay" && (
        <div className="mt-6 rounded-xl border border-gray-200 p-4">
          <p className="text-sm font-bold text-gray-900">Razorpay Payment</p>
          <p className="mt-1 text-sm text-gray-500">Razorpay checkout will open after you click Book Now.</p>
        </div>
      )}
    </>
  );
  const layoutMarker = (
    <div className={layoutLabelPosition === "top" ? "mb-10 text-center" : "mt-8 text-center"}>
      <div className="mx-auto h-1 w-[460px] max-w-[70vw] rounded-full bg-gradient-to-r from-transparent via-[#7c5cff] to-transparent" />
      <p className="mt-2 text-[10px] font-bold uppercase tracking-[0.35em] text-[#7c5cff]">
        {layoutLabelPosition === "bottom" ? `${layoutLabel} this way` : layoutLabel}
      </p>
    </div>
  );

  return (
    <section className="min-h-screen bg-white pt-20 pb-24">
      <div className="sticky top-20 z-30 border-b border-gray-200 bg-white/95 backdrop-blur">
        <div className="mx-auto max-w-7xl px-4 py-4 flex items-center justify-between gap-4">
          <button
            onClick={() => navigate(-1)}
            className="flex items-center gap-2 text-sm font-semibold text-gray-700"
          >
            <ChevronLeft size={18} /> Back
          </button>
          <div className="text-center min-w-0">
            <h1 className="font-bold text-gray-900 truncate">{eventTitle}</h1>
            <p className="text-xs text-gray-500 truncate">
              {formatDate(show)} | {venueText}
            </p>
          </div>
          <div className="w-16" />
        </div>
      </div>

      {bookingMode === "reserved_seating" ? (
        <div className="mx-auto max-w-7xl px-4 py-8">
          <div className="min-w-[920px] overflow-x-auto">
            <div className="space-y-10 pb-8">
              {showLayoutLabel && layoutLabelPosition === "top" && layoutMarker}

              {seatSections.map((section) => (
                <div key={`${section.label}-${section.price}`}>
                  <h2 className="mb-5 text-center text-sm font-bold text-gray-800 uppercase">
                    {section.label} : {formatMoney(section.price, currency)}
                  </h2>
                  <div className="space-y-3">
                    {Object.entries(section.rows).map(([rowLabel, rowSeats]) => (
                      <div key={rowLabel} className="grid grid-cols-[36px_1fr] items-center gap-5">
                        <div className="text-sm font-semibold text-gray-500">
                          {rowLabel}
                        </div>
                        <div className="flex flex-wrap gap-2">
                          {rowSeats.map((seat) => {
                            const selected = selectedSeatIds.includes(seat.id);
                            const occupied = seat.status !== "available";
                            const isBest = ["VIP", "Gold", "Premium"].some((type) =>
                              (seat.seat_type || "").toLowerCase().includes(type.toLowerCase()),
                            );
                            const seatColor = seat.seat_type_color || "#111827";
                            const availableStyle =
                              !selected && !occupied
                                ? {
                                    borderColor: isBest ? "#38bdf8" : seatColor,
                                    boxShadow: isBest
                                      ? "0 0 8px rgba(56,189,248,0.55)"
                                      : `0 0 0 1px ${seatColor}22`,
                                  }
                                : undefined;

                            return (
                              <button
                                key={seat.id}
                                type="button"
                                disabled={occupied}
                                onClick={() => toggleSeat(seat)}
                                title={`${seat.seat_number} - ${seat.status}`}
                                style={availableStyle}
                                className={`h-7 w-7 rounded-md border text-[11px] font-medium transition flex items-center justify-center ${
                                  selected
                                    ? "border-[#5b3df5] bg-[#5b3df5] text-white shadow-md"
                                    : occupied
                                      ? "border-gray-200 bg-gray-50 text-gray-300 cursor-not-allowed"
                                      : isBest
                                        ? "border-sky-400 bg-sky-100 text-gray-800 shadow-[0_0_8px_rgba(56,189,248,0.55)]"
                                        : "border-gray-900 bg-white text-gray-800 hover:border-[#5b3df5]"
                                }`}
                              >
                                {occupied ? <X size={12} /> : seat.column_number}
                              </button>
                            );
                          })}
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              ))}

              {showLayoutLabel && layoutLabelPosition === "bottom" && layoutMarker}
            </div>

          </div>
        </div>
      ) : (
        <div className="mx-auto max-w-3xl px-4 py-10">
          <div className="rounded-2xl border border-gray-200 p-6 shadow-sm">
            <h2 className="text-xl font-bold text-gray-900">Select Tickets</h2>
            <p className="mt-1 text-sm text-gray-500">{venueText}</p>

            {bookingMode === "tiered_tickets" && (
              <div className="mt-6 space-y-3">
                {show.ticket_types?.map((ticketType) => (
                  <label
                    key={ticketType.id}
                    className={`flex items-center justify-between rounded-xl border p-4 cursor-pointer ${
                      Number(selectedTicketTypeId) === Number(ticketType.id)
                        ? "border-orange-500 bg-orange-50"
                        : "border-gray-200"
                    }`}
                  >
                    <span>
                      <span className="block font-bold text-gray-900">
                        {ticketType.name}
                      </span>
                      <span className="block text-sm text-gray-500">
                        {ticketType.description || "Ticket"}
                      </span>
                    </span>
                    <span className="flex items-center gap-4">
                      <span className="font-bold">{ticketType.formatted_price}</span>
                      <input
                        type="radio"
                        checked={Number(selectedTicketTypeId) === Number(ticketType.id)}
                        onChange={() => setSelectedTicketTypeId(ticketType.id)}
                      />
                    </span>
                  </label>
                ))}
              </div>
            )}

            {bookingMode === "general_admission" && (
              <div className="mt-6 rounded-xl border border-gray-200 p-4 flex items-center justify-between">
                <div>
                  <p className="font-bold text-gray-900">General Admission</p>
                  <p className="text-sm text-gray-500">{show.formatted_price}</p>
                </div>
              </div>
            )}

            <div className="mt-6 flex items-center justify-between rounded-xl bg-gray-50 p-4">
              <span className="font-semibold text-gray-800">Quantity</span>
              <div className="flex items-center gap-4">
                <button
                  type="button"
                  onClick={() => setQuantity((prev) => Math.max(prev - 1, 1))}
                  className="p-2 rounded-full border border-gray-200 bg-white"
                >
                  <Minus size={16} />
                </button>
                <span className="w-8 text-center font-bold">{quantity}</span>
                <button
                  type="button"
                  onClick={() => setQuantity((prev) => prev + 1)}
                  className="p-2 rounded-full border border-gray-200 bg-white"
                >
                  <Plus size={16} />
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {error && (
        <div className="fixed bottom-24 left-1/2 z-40 w-[calc(100%-32px)] max-w-xl -translate-x-1/2 rounded-2xl bg-red-50 p-4 text-sm font-semibold text-red-600 shadow-lg">
          {error}
        </div>
      )}

      {paymentOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6">
          <div className="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl">
            <div className="flex items-start justify-between gap-4">
              <div>
                <h2 className="text-xl font-black text-gray-900">Complete Payment</h2>
                <p className="mt-1 text-sm text-gray-500">
                  {selectedCount} {selectedCount === 1 ? "ticket" : "tickets"} |{" "}
                  {formatMoney(total, currency)}
                </p>
              </div>
              <button
                type="button"
                onClick={() => setPaymentOpen(false)}
                className="rounded-full border border-gray-200 p-2 text-gray-500"
              >
                <X size={18} />
              </button>
            </div>

            {paymentPanel}

            <button
              type="button"
              disabled={submitting || !paymentReady}
              onClick={submitBooking}
              className="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-full bg-[#5b3df5] px-6 py-3 text-sm font-bold text-white disabled:opacity-50"
            >
              <CreditCard size={16} />
              {submitting ? "Processing..." : "Confirm Payment"}
            </button>
          </div>
        </div>
      )}

      <div className="fixed bottom-0 left-0 right-0 z-40 border-t border-gray-200 bg-white">
        <div className="mx-auto max-w-7xl px-4 py-3 flex flex-wrap items-center justify-between gap-4">
          <div className="flex items-center gap-5 text-xs text-gray-700">
            {bookingMode === "reserved_seating" && (
              <>
                <span className="flex items-center gap-2">
                  <span className="h-3 w-3 rounded border border-sky-400 bg-sky-100" /> Best Seats
                </span>
                <span className="flex items-center gap-2">
                  <span className="h-3 w-3 rounded border border-gray-900 bg-white" /> Available
                </span>
                <span className="flex items-center gap-2">
                  <span className="h-3 w-3 rounded border border-gray-200 bg-gray-50" /> Occupied
                </span>
                <span className="flex items-center gap-2">
                  <span className="h-3 w-3 rounded bg-[#5b3df5]" /> Selected
                </span>
              </>
            )}
          </div>

          <div className="flex items-center gap-4">
            <div className="text-right">
              <p className="text-xs text-gray-500">
                {selectedCount} {selectedCount === 1 ? "ticket" : "tickets"}
              </p>
              <p className="text-lg font-black text-gray-900">
                {formatMoney(total, currency)}
              </p>
            </div>
            <button
              type="button"
              disabled={submitting || selectedCount === 0}
              onClick={openPaymentStep}
              className="inline-flex items-center gap-2 rounded-full bg-[#5b3df5] px-6 py-3 text-sm font-bold text-white disabled:opacity-50"
            >
              <CreditCard size={16} />
              Continue
            </button>
          </div>
        </div>
      </div>
    </section>
  );
};

export default BookingPage;
