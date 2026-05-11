// components/InternationalEvents.jsx
import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import {
  Heart,
  Zap,
  Star,
  TrendingUp,
  MessageSquare,
  Ticket,
} from "lucide-react";
import useWishlist from "../hooks/useWishlist";
import { createWishlistItem, toggleWishlist } from "../utils/wishlist";

const API_BASE_URL = import.meta.env.VITE_API_URL;

const fallbackImage =
  "https://images.unsplash.com/photo-1459749411175-04bf5292ceea?auto=format&fit=crop&q=80&w=600";

const badges = [
  {
    label: "Selling fast",
    icon: <Zap size={14} className="fill-yellow-400 text-yellow-400" />,
  },
  {
    label: "Editor's pick",
    icon: <Star size={14} className="fill-orange-400 text-orange-400" />,
  },
  {
    label: "Trending",
    icon: <TrendingUp size={14} className="text-green-400" />,
  },
  {
    label: "Most talked",
    icon: <MessageSquare size={14} className="text-blue-400" />,
  },
];

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

const formatPrice = (show) => {
  if (show?.formatted_price) return show.formatted_price;

  if (!show?.price) return "TBA";

  const currency = show.currency_code || "INR";

  return new Intl.NumberFormat("en-IN", {
    style: "currency",
    currency,
    maximumFractionDigits: 0,
  }).format(Number(show.price));
};

const InternationalEvents = () => {
  const navigate = useNavigate();
  const wishlistItems = useWishlist();
  const [events, setEvents] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    const fetchInternationalEvents = async () => {
      try {
        const response = await fetch(
          `${API_BASE_URL}/api/events?featured=true&international=true`,
          {
            headers: {
              Accept: "application/json",
            },
          },
        );

        if (!response.ok) {
          throw new Error("Failed to load international events");
        }

        const result = await response.json();
        setEvents(result.data || []);
      } catch (err) {
        setError(err.message || "Something went wrong");
      } finally {
        setLoading(false);
      }
    };

    fetchInternationalEvents();
  }, []);

  return (
    <div className="bg-white p-6 md:p-10 font-sans max-w-screen-2xl mx-auto">
      <h2 className="text-2xl font-bold text-gray-900 mb-8 border-l-4 border-red-500 pl-4">
        Trending Events Around the World
      </h2>

      {loading && (
        <p className="text-gray-500 font-medium">Loading events...</p>
      )}

      {error && !loading && <p className="text-red-600 font-medium">{error}</p>}

      {!loading && !error && events.length === 0 && (
        <p className="text-gray-500 font-medium">
          No international featured events found.
        </p>
      )}

      {!loading && !error && events.length > 0 && (
        <div className="flex overflow-x-auto gap-8 pb-4 md:grid md:grid-cols-2 lg:grid-cols-4 md:gap-8 md:overflow-visible snap-x snap-mandatory scrollbar-hide">
          {events.map((event, index) => {
            const show = event.primary_listing;
            const venue = show?.venue;
            const badge = badges[index % badges.length];
            const isLiked = wishlistItems.some(
              (item) => String(item.id) === String(event.id),
            );

            return (
              <div
                key={event.id}
                onClick={() => navigate(`/event/${event.slug || event.id}`)}
                className="flex flex-col group w-[75vw] max-w-[280px] flex-shrink-0 snap-start md:w-auto md:max-w-none md:flex-shrink cursor-pointer"
              >
                <div className="relative aspect-[16/10] overflow-hidden rounded-2xl bg-gray-100">
                  <img
                    src={
                      event.poster_image_url ||
                      event.banner_image_url ||
                      fallbackImage
                    }
                    alt={event.title}
                    loading="lazy"
                    className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                  />

                  <div className="absolute top-4 left-4 bg-black/70 backdrop-blur-md text-white text-[10px] font-bold px-3 py-1.5 rounded-lg flex items-center gap-2 border border-white/20">
                    {badge.label.toUpperCase()}
                    {badge.icon}
                  </div>

                  <div className="absolute bottom-4 right-4 translate-y-2 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
                    <button
                      type="button"
                      onClick={(clickEvent) => {
                        clickEvent.stopPropagation();
                        toggleWishlist(createWishlistItem({ event, show, venue }));
                      }}
                      className={`bg-white p-2.5 rounded-full shadow-xl hover:bg-gray-50 active:scale-90 transition-all ${
                        isLiked ? "text-red-500" : "text-gray-800"
                      }`}
                    >
                      <Heart size={18} fill={isLiked ? "currentColor" : "none"} />
                    </button>
                  </div>
                </div>

                <div className="mt-4 space-y-1.5 px-1">
                  <p className="text-[11px] font-bold text-gray-400 tracking-wider uppercase">
                    {formatDate(show)}
                  </p>

                  <h3 className="text-base font-bold text-gray-900 leading-snug line-clamp-2 h-12 group-hover:text-red-600 transition-colors">
                    {event.title}
                  </h3>

                  <p className="text-sm text-gray-500 truncate font-medium">
                    {venue?.name
                      ? `${venue.name}, ${venue.country || venue.city}`
                      : venue?.city || "Location coming soon"}
                  </p>

                  <div className="flex items-center gap-1.5 pt-2 border-t border-gray-50 mt-2">
                    <Ticket size={14} className="text-gray-400" />
                    <span className="text-[14px] font-extrabold text-gray-800">
                      {formatPrice(show)}
                    </span>
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      )}

      <style jsx>{`
        .scrollbar-hide::-webkit-scrollbar {
          display: none;
        }
        .scrollbar-hide {
          -ms-overflow-style: none;
          scrollbar-width: none;
        }
      `}</style>
    </div>
  );
};

export default InternationalEvents;
