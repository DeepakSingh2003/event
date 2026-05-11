import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { Heart, Ticket } from "lucide-react";
import useWishlist from "../hooks/useWishlist";
import { toggleWishlist } from "../utils/wishlist";

const API_BASE_URL = import.meta.env.VITE_API_URL;

const tabs = [
  "All",
  "This Week",
  "This Weekend",
  "Next Week",
  "Next Weekend",
  "This Month",
];

const fallbackImage =
  "https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?q=80&w=600";

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
  if (!show?.price || Number(show.price) === 0) return "Free";

  return new Intl.NumberFormat("en-IN", {
    style: "currency",
    currency: show.currency_code || "INR",
    maximumFractionDigits: 0,
  }).format(Number(show.price));
};

const getEventCategory = (showDate) => {
  if (!showDate) return "Other";

  const today = new Date();
  today.setHours(0, 0, 0, 0);

  const eventDate = new Date(showDate);
  eventDate.setHours(0, 0, 0, 0);

  const diffDays = Math.floor((eventDate - today) / (1000 * 60 * 60 * 24));
  const day = eventDate.getDay();
  const isWeekend = day === 0 || day === 6;

  const isCurrentMonth =
    eventDate.getMonth() === today.getMonth() &&
    eventDate.getFullYear() === today.getFullYear();

  if (diffDays >= 0 && diffDays <= 7 && isWeekend) return "This Weekend";
  if (diffDays > 7 && diffDays <= 14 && isWeekend) return "Next Weekend";
  if (diffDays >= 0 && diffDays <= 7) return "This Week";
  if (diffDays > 7 && diffDays <= 14) return "Next Week";
  if (isCurrentMonth && diffDays >= 0) return "This Month";

  return "Other";
};

const getInterestedCount = (eventId) => {
  const count = 80 + ((eventId * 37) % 950);

  if (count >= 1000) {
    return `${(count / 1000).toFixed(1)}k+`;
  }

  return `${count}+`;
};

const FestivalEvents = () => {
  const navigate = useNavigate();
  const wishlistItems = useWishlist();
  const [activeTab, setActiveTab] = useState("All");
  const [events, setEvents] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    const fetchFestivalEvents = async () => {
      try {
        const categoryResponse = await fetch(
          `${API_BASE_URL}/api/categories?search=Festivals`,
          {
            headers: {
              Accept: "application/json",
            },
          },
        );

        if (!categoryResponse.ok) {
          throw new Error("Failed to load festival category");
        }

        const categoryResult = await categoryResponse.json();

        const festivalCategory = categoryResult.data?.find(
          (category) => category.name.toLowerCase() === "festivals",
        );

        if (!festivalCategory) {
          setEvents([]);
          return;
        }

        const eventsResponse = await fetch(
          `${API_BASE_URL}/api/events?category_id=${festivalCategory.id}`,
          {
            headers: {
              Accept: "application/json",
            },
          },
        );

        if (!eventsResponse.ok) {
          throw new Error("Failed to load festival events");
        }

        const eventsResult = await eventsResponse.json();

        const mappedEvents = (eventsResult.data || []).map((event) => {
          const show = event.primary_listing;
          const venue = show?.venue;

          return {
            id: event.id,
            slug: event.slug,
            category: getEventCategory(show?.show_date),
            title: event.title,
            date: formatDate(show),
            location:
              venue?.name && venue?.city
                ? `${venue.name}, ${venue.city}`
                : venue?.name || venue?.city || "Location coming soon",
            price: formatPrice(show),
            interested: getInterestedCount(event.id),
            image:
              event.poster_image_url ||
              event.banner_image_url ||
              event.gallery?.[0]?.image_url ||
              fallbackImage,
            featured: event.is_featured,
          };
        });

        setEvents(mappedEvents);
      } catch (err) {
        setError(err.message || "Something went wrong");
      } finally {
        setLoading(false);
      }
    };

    fetchFestivalEvents();
  }, []);

  const filteredEvents =
    activeTab === "All"
      ? events
      : events.filter((event) => event.category === activeTab);

  const EventCard = ({ event }) => {
    const isLiked = wishlistItems.some((item) => String(item.id) === String(event.id));

    return (
      <div
        onClick={() => navigate(`/event/${event.slug || event.id}`)}
        className="flex flex-col group cursor-pointer"
      >
        <div className="relative aspect-[16/9] rounded-2xl overflow-hidden bg-gray-100">
          <img
            src={event.image}
            alt={event.title}
            loading="lazy"
            className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
          />

          {event.featured && (
            <div className="absolute top-3 left-3 bg-black/60 backdrop-blur-sm text-white text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider">
              Featured
            </div>
          )}

          <button
            type="button"
            onClick={(clickEvent) => {
              clickEvent.stopPropagation();
              toggleWishlist({
                id: event.id,
                title: event.title,
                slug: event.slug,
                category: "Festival",
                image: event.image,
                date: event.date,
                location: event.location,
                price: event.price,
              });
            }}
            className={`absolute bottom-3 right-3 bg-white p-2 rounded-full shadow-md hover:text-red-500 transition-colors ${
              isLiked ? "text-red-500" : "text-gray-500"
            }`}
          >
            <Heart size={16} fill={isLiked ? "currentColor" : "none"} />
          </button>
        </div>

        <div className="mt-4 space-y-1">
          <p className="text-xs font-semibold text-gray-400 uppercase tracking-tight">
            {event.date}
          </p>

          <h3 className="text-[15px] font-bold text-gray-900 leading-snug line-clamp-2 h-11">
            {event.title}
          </h3>

          <p className="text-sm text-gray-400 font-medium truncate">
            {event.location}
          </p>

          <div className="flex items-center justify-between pt-3 border-t border-gray-50 mt-2">
            <div className="flex items-center gap-2">
              <div className="flex -space-x-2">
                <div className="w-6 h-6 rounded-full border-2 border-white bg-gray-300 overflow-hidden">
                  <img src="https://i.pravatar.cc/100?img=1" alt="user" />
                </div>
                <div className="w-6 h-6 rounded-full border-2 border-white bg-gray-400 overflow-hidden">
                  <img src="https://i.pravatar.cc/100?img=2" alt="user" />
                </div>
              </div>

              <span className="text-[12px] font-medium text-gray-500">
                {event.interested} Interested
              </span>
            </div>

            <div className="flex items-center gap-1 text-gray-900">
              <Ticket size={14} className="text-gray-400" />
              <span className="text-xs font-bold">{event.price}</span>
              </div>
            </div>
          </div>
        </div>
    );
  };

  return (
    <div className="bg-white min-h-auto md:min-h-screen p-4 md:p-10 font-sans text-gray-800">
      <div className="max-w-7xl mx-auto">
        <h2 className="text-2xl font-bold mb-6">Popular Festivals</h2>

        <div className="flex overflow-x-auto space-x-8 border-b border-gray-100 mb-10 no-scrollbar">
          {tabs.map((tab) => (
            <button
              key={tab}
              onClick={() => setActiveTab(tab)}
              className={`pb-3 text-sm font-semibold whitespace-nowrap transition-all relative ${
                activeTab === tab
                  ? "text-gray-900"
                  : "text-gray-400 hover:text-gray-600"
              }`}
            >
              {tab}
              {activeTab === tab && (
                <div className="absolute bottom-0 left-0 w-full h-0.5 bg-gray-800" />
              )}
            </button>
          ))}
        </div>

        {loading && (
          <p className="text-gray-500 font-medium">Loading events...</p>
        )}

        {error && !loading && (
          <p className="text-red-600 font-medium">{error}</p>
        )}

        {!loading && !error && filteredEvents.length === 0 && (
          <p className="text-gray-500 font-medium">No festival events found.</p>
        )}

        {!loading && !error && filteredEvents.length > 0 && (
          <>
            <div className="md:hidden overflow-x-auto pb-4 scrollbar-hide">
              <div className="inline-flex gap-6">
                {filteredEvents.map((event) => (
                  <div
                    key={event.id}
                    className="w-[75vw] max-w-[280px] flex-shrink-0"
                  >
                    <EventCard event={event} />
                  </div>
                ))}
              </div>
            </div>

            <div className="hidden md:grid md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-12">
              {filteredEvents.map((event) => (
                <EventCard key={event.id} event={event} />
              ))}
            </div>
          </>
        )}
      </div>

      <style jsx>{`
        .scrollbar-hide::-webkit-scrollbar {
          display: none;
        }
        .scrollbar-hide {
          -ms-overflow-style: none;
          scrollbar-width: none;
        }
        .no-scrollbar::-webkit-scrollbar {
          display: none;
        }
        .no-scrollbar {
          -ms-overflow-style: none;
          scrollbar-width: none;
        }
      `}</style>
    </div>
  );
};

export default FestivalEvents;
