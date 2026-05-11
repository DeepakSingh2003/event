import { useEffect, useMemo, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import {
  Heart,
  Share2,
  Calendar,
  MapPin,
  ChevronDown,
  MessageCircle,
  Star,
  ShieldCheck,
  Info,
  Clock,
  Users,
  Award,
  Coffee,
  Map as MapIcon,
} from "lucide-react";
import useWishlist from "../hooks/useWishlist";
import {
  createWishlistItem,
  isWishlisted,
  toggleWishlist,
} from "../utils/wishlist";

const API_BASE_URL = import.meta.env.VITE_API_URL || "http://localhost:8000";
const fallbackImage =
  "https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&q=80&w=1400";

const formatDate = (show) => {
  if (!show?.show_date) return "Date coming soon";

  const date = new Date(`${show.show_date}T${show.show_time || "00:00"}`);

  return date.toLocaleString("en-IN", {
    weekday: "short",
    day: "2-digit",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  });
};

const formatPrice = (show) => {
  if (show?.formatted_price) return show.formatted_price;
  if (!show?.price || Number(show.price) === 0) return "Free";

  return new Intl.NumberFormat("en-IN", {
    style: "currency",
    currency: show.currency_code || "INR",
    maximumFractionDigits: 0,
  }).format(Number(show.price));
};

const EventDetails = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [event, setEvent] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [openIndex, setOpenIndex] = useState(null);
  const wishlistItems = useWishlist();

  useEffect(() => {
    const fetchEvent = async () => {
      setLoading(true);
      setError("");

      try {
        const response = await fetch(`${API_BASE_URL}/api/events/${id}`, {
          headers: { Accept: "application/json" },
        });

        if (!response.ok) {
          throw new Error("Event not found");
        }

        const result = await response.json();
        setEvent(result.data);
      } catch (fetchError) {
        setError(fetchError.message || "Something went wrong");
      } finally {
        setLoading(false);
      }
    };

    fetchEvent();
  }, [id]);

  const show = event?.primary_listing || event?.shows?.[0];
  const venue = useMemo(() => show?.venue || {}, [show]);
  const eventDate = formatDate(show);
  const price = formatPrice(show);

  const eventLocation = useMemo(() => {
    if (!event) return null;

    const cityName =
      typeof venue?.city === "object" ? venue?.city?.name : venue?.city;

    return {
      location:
        venue?.name && cityName
          ? `${venue.name}, ${cityName}`
          : venue?.name || cityName || "Location coming soon",
    };
  }, [event, venue]);

  const mapQuery = useMemo(() => {
    const cityName =
      typeof venue?.city === "object" ? venue?.city?.name : venue?.city;

    if (venue?.latitude && venue?.longitude) {
      return `${venue.latitude},${venue.longitude}`;
    }

    return [venue?.name, venue?.address, cityName].filter(Boolean).join(", ");
  }, [venue]);

  const mapEmbedUrl = mapQuery
    ? `https://maps.google.com/maps?q=${encodeURIComponent(mapQuery)}&z=14&output=embed`
    : "";
  const mapOpenUrl =
    venue?.map_url ||
    (mapQuery
      ? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(mapQuery)}`
      : "");
  const isLiked = event?.id
    ? wishlistItems.some((item) => String(item.id) === String(event.id)) ||
      isWishlisted(event.id)
    : false;

  const handleWishlistToggle = () => {
    const item = createWishlistItem({ event, show, venue });
    toggleWishlist(item);
  };

  const faqItems = [
    {
      question: "How do I get my tickets?",
      answer: "Your booking confirmation is generated instantly after payment.",
    },
    {
      question: "Can I choose seats?",
      answer: "Available seat selection depends on the show and venue setup.",
    },
  ];

  if (loading) {
    return (
      <div className="h-screen flex items-center justify-center">
        <p className="text-gray-500 font-medium">Loading event...</p>
      </div>
    );
  }

  if (error || !event) {
    return (
      <div className="h-screen flex items-center justify-center">
        <div className="text-center">
          <h2 className="text-2xl font-bold text-gray-800">
            {error || "Event not found"}
          </h2>
          <button
            onClick={() => navigate(-1)}
            className="mt-4 text-orange-500 font-medium"
          >
            Go Back
          </button>
        </div>
      </div>
    );
  }

  return (
    <section className="bg-white min-h-screen mt-16 pb-12">
      <div className="max-w-6xl mx-auto px-4 py-8">
        <div className="relative rounded-3xl overflow-hidden shadow-2xl shadow-orange-100/50">
          <img
            src={event.poster_image_url || event.banner_image_url || fallbackImage}
            alt={event.title}
            className="w-full h-[300px] md:h-[450px] object-cover"
          />
          <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent" />
          <div className="absolute bottom-8 left-8 right-8 text-white">
            <span className="bg-orange-500 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest mb-4 inline-block">
              {event.category || "Featured Event"}
            </span>
            <h1 className="text-3xl md:text-5xl font-extrabold leading-tight">
              {event.title}
            </h1>
          </div>
        </div>

        <div className="grid lg:grid-cols-3 gap-12 mt-10">
          <div className="lg:col-span-2 space-y-12">
            <div className="flex flex-wrap gap-4">
              <button
                onClick={handleWishlistToggle}
                className={`flex items-center gap-2 px-6 py-3 rounded-2xl font-semibold transition shadow-lg ${
                  isLiked
                    ? "bg-red-500 text-white shadow-red-200"
                    : "bg-black text-white shadow-gray-200 hover:bg-gray-800"
                }`}
              >
                <Heart size={18} fill={isLiked ? "white" : "none"} />
                {isLiked ? "Interested" : "I'm Interested"}
              </button>
              <button className="flex items-center gap-2 bg-gray-50 text-gray-700 px-6 py-3 rounded-2xl font-semibold hover:bg-gray-100 transition">
                <Share2 size={18} /> Share
              </button>
              <button className="flex items-center gap-2 bg-gray-50 text-gray-700 px-6 py-3 rounded-2xl font-semibold hover:bg-gray-100 transition">
                <Calendar size={18} /> Add to Calendar
              </button>
            </div>

            <div className="grid md:grid-cols-2 gap-8 py-4 border-y border-gray-100">
              <div className="flex gap-4">
                <div className="w-12 h-12 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-600 shrink-0">
                  <Calendar size={24} />
                </div>
                <div>
                  <h4 className="font-bold text-gray-900">When</h4>
                  <p className="text-gray-600 text-sm">{eventDate}</p>
                </div>
              </div>
              <div className="flex gap-4">
                <div className="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 shrink-0">
                  <MapPin size={24} />
                </div>
                <div>
                  <h4 className="font-bold text-gray-900">Where</h4>
                  <p className="text-gray-600 text-sm leading-snug">
                    {eventLocation?.location}
                    {venue?.address ? `, ${venue.address}` : ""}
                  </p>
                </div>
              </div>
            </div>

            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
              {[
                { icon: <Clock size={20} />, label: "Live", sub: "Experience" },
                { icon: <Award size={20} />, label: event.category || "Event", sub: "Category" },
                { icon: <Coffee size={20} />, label: price, sub: "Starting price" },
                { icon: <Users size={20} />, label: `${show?.available_seats ?? "Many"}`, sub: "Seats" },
              ].map((item, idx) => (
                <div
                  key={idx}
                  className="p-4 bg-gray-50 rounded-2xl border border-gray-100"
                >
                  <div className="text-orange-500 mb-2">{item.icon}</div>
                  <p className="font-bold text-gray-900 text-sm">{item.label}</p>
                  <p className="text-xs text-gray-500">{item.sub}</p>
                </div>
              ))}
            </div>

            <div className="prose prose-orange max-w-none">
              <h3 className="text-2xl font-bold text-gray-900 mb-6">
                About this event
              </h3>
              <div className="text-gray-600 leading-relaxed space-y-4 text-lg">
                {(event.description || "Join us for an unforgettable experience.")
                  .split("\n")
                  .map((para, index) => (
                    <p key={index}>{para}</p>
                  ))}
              </div>
            </div>

            {event.timeline?.length > 0 && (
              <div className="space-y-6">
                <h3 className="text-2xl font-bold text-gray-900">
                  Event Schedule
                </h3>
                <div className="space-y-4 border-l-2 border-gray-100 ml-2 pl-6">
                  {event.timeline.map((item, index) => (
                    <div key={`${item.title}-${index}`} className="relative">
                      <div className="absolute -left-[31px] top-1 w-3 h-3 rounded-full bg-orange-500 ring-4 ring-white" />
                      <p className="text-gray-900 font-semibold">{item.title}</p>
                      {item.description && (
                        <p className="text-gray-500 text-sm">{item.description}</p>
                      )}
                    </div>
                  ))}
                </div>
              </div>
            )}

            <div className="space-y-6">
              <h3 className="text-2xl font-bold text-gray-900">Venue Spotlight</h3>
              <div className="bg-gray-900 rounded-3xl p-6 text-white flex flex-col md:flex-row gap-6 items-center">
                <div className="flex-1">
                  <div className="flex items-center gap-2 text-orange-400 text-sm font-bold mb-2 uppercase tracking-widest">
                    <MapIcon size={16} /> <span>Location Info</span>
                  </div>
                  <h4 className="text-xl font-bold mb-2">
                    {venue?.name || "Venue coming soon"}
                  </h4>
                  <p className="text-gray-400 text-sm mb-4">
                    {venue?.address || venue?.city || "Address coming soon"}
                  </p>
                </div>
                <div className="w-full md:w-64 h-40 overflow-hidden rounded-2xl border border-gray-700 bg-gray-800">
                  {mapEmbedUrl ? (
                    <iframe
                      title={`${venue?.name || "Venue"} map`}
                      src={mapEmbedUrl}
                      className="h-full w-full"
                      loading="lazy"
                      referrerPolicy="no-referrer-when-downgrade"
                    />
                  ) : (
                    <div className="flex h-full items-center justify-center text-gray-500">
                      Map coming soon
                    </div>
                  )}
                </div>
              </div>
              {mapOpenUrl && (
                <a
                  href={mapOpenUrl}
                  target="_blank"
                  rel="noreferrer"
                  className="mt-4 inline-flex items-center gap-2 text-sm font-bold text-orange-400 hover:text-orange-300"
                >
                  <MapPin size={16} />
                  Open in Google Maps
                </a>
              )}
            </div>

            <div className="pt-8">
              <h3 className="text-2xl font-bold mb-6">Common Questions</h3>
              <div className="space-y-2">
                {faqItems.map((item, index) => (
                  <div
                    key={item.question}
                    className={`rounded-2xl transition-all border border-gray-100 ${openIndex === index ? "bg-orange-50/50" : ""}`}
                  >
                    <button
                      onClick={() =>
                        setOpenIndex(openIndex === index ? null : index)
                      }
                      className="w-full flex items-center justify-between p-5 text-left font-semibold text-gray-800"
                    >
                      {item.question}
                      <ChevronDown
                        size={20}
                        className={`transition-transform ${openIndex === index ? "rotate-180" : ""}`}
                      />
                    </button>
                    {openIndex === index && (
                      <div className="px-5 pb-5 text-gray-600 leading-relaxed">
                        {item.answer}
                      </div>
                    )}
                  </div>
                ))}
              </div>
            </div>
          </div>

          <div className="space-y-6">
            <div className="bg-white p-8 rounded-[2.5rem] shadow-2xl shadow-gray-200/50 border border-gray-50 sticky top-24">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <p className="text-gray-500 font-medium mb-1 text-sm">
                    Price per person
                  </p>
                  <h2 className="text-4xl font-black text-gray-900">{price}</h2>
                </div>
                <div className="bg-green-50 text-green-600 px-3 py-1 rounded-lg text-[10px] font-bold uppercase">
                  Available
                </div>
              </div>

              <button
                onClick={() => show?.id && navigate(`/booking/${show.id}`)}
                disabled={!show?.id}
                className="w-full bg-orange-500 hover:bg-orange-600 text-white py-4 rounded-2xl mt-4 font-bold text-lg shadow-lg shadow-orange-200 transition-all active:scale-95 flex items-center justify-center gap-2"
              >
                Get Tickets Now
              </button>

              <div className="mt-6 space-y-4">
                <div className="flex items-center gap-3 text-xs text-gray-500">
                  <ShieldCheck size={16} className="text-green-500" />
                  <span>Secure Payment & Instant Confirmation</span>
                </div>
                <div className="flex items-center gap-3 text-xs text-gray-500">
                  <Info size={16} className="text-blue-500" />
                  <span>Non-refundable policy applies</span>
                </div>
              </div>

              <hr className="my-8 border-gray-100" />

              <div className="space-y-4">
                <p className="text-xs font-bold uppercase tracking-widest text-gray-400">
                  Organized By
                </p>
                <div className="flex items-center gap-4">
                  <div className="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center text-white font-bold text-xl">
                    {event.title.charAt(0)}
                  </div>
                  <div className="flex-1">
                    <h5 className="font-bold text-gray-900 leading-none">
                      {event.meta_title || event.title}
                    </h5>
                    <p className="text-xs text-gray-500 mt-1">Event Organizer</p>
                  </div>
                </div>
                <div className="grid grid-cols-2 gap-3 mt-4">
                  <button className="flex items-center justify-center gap-2 border border-gray-100 py-2 rounded-xl text-xs font-bold hover:bg-gray-50 transition">
                    <MessageCircle size={14} /> Message
                  </button>
                  <div className="flex items-center justify-center gap-1 bg-gray-50 py-2 rounded-xl text-xs font-bold">
                    <Star size={14} fill="orange" stroke="none" /> 4.8
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </section>
  );
};

export default EventDetails;
