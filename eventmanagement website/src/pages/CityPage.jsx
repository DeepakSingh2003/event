import { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { Heart, Ticket } from "lucide-react";
import useWishlist from "../hooks/useWishlist";
import { createWishlistItem, toggleWishlist } from "../utils/wishlist";

const API_BASE_URL = import.meta.env.VITE_API_URL || "http://localhost:8000";
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

const CityPage = () => {
  const { cityName } = useParams();
  const navigate = useNavigate();

  const [city, setCity] = useState(null);
  const [shows, setShows] = useState([]);
  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [error, setError] = useState("");
  const [currentPage, setCurrentPage] = useState(1);
  const [hasMore, setHasMore] = useState(false);

  useEffect(() => {
    const fetchCityEvents = async () => {
      setLoading(true);
      setError("");
      setCurrentPage(1);
      setHasMore(false);

      try {
        const cityResponse = await fetch(`${API_BASE_URL}/api/cities/${cityName}`, {
          headers: { Accept: "application/json" },
        });

        if (!cityResponse.ok) {
          throw new Error("Failed to load city events");
        }

        const cityResult = await cityResponse.json();
        const cityData = cityResult.data;
        const cityId = cityData?.id;

        if (!cityId) {
          throw new Error("City not found");
        }

        const showsResponse = await fetch(
          `${API_BASE_URL}/api/shows?city_id=${cityId}&page=1`,
          {
            headers: { Accept: "application/json" },
          },
        );

        if (!showsResponse.ok) {
          throw new Error("Failed to load city events");
        }

        const showsResult = await showsResponse.json();

        setCity(cityData);
        setShows(Array.isArray(showsResult.data) ? showsResult.data : []);
        setHasMore(
          Boolean(
            showsResult.links?.next ||
              showsResult.meta?.current_page < showsResult.meta?.last_page,
          ),
        );
      } catch (fetchError) {
        setError(fetchError.message || "Something went wrong");
      } finally {
        setLoading(false);
      }
    };

    fetchCityEvents();
  }, [cityName]);

  const handleSeeMore = async () => {
    if (loadingMore || !hasMore || !city?.id) return;

    const nextPage = currentPage + 1;
    setLoadingMore(true);
    setError("");

    try {
      const showsResponse = await fetch(
        `${API_BASE_URL}/api/shows?city_id=${city.id}&page=${nextPage}`,
        {
          headers: { Accept: "application/json" },
        },
      );

      if (!showsResponse.ok) {
        throw new Error("Failed to load more city events");
      }

      const showsResult = await showsResponse.json();

      setShows((existingShows) => [
        ...existingShows,
        ...(Array.isArray(showsResult.data) ? showsResult.data : []),
      ]);
      setCurrentPage(nextPage);
      setHasMore(
        Boolean(
          showsResult.links?.next ||
            showsResult.meta?.current_page < showsResult.meta?.last_page,
        ),
      );
    } catch (fetchError) {
      setError(fetchError.message || "Something went wrong");
    } finally {
      setLoadingMore(false);
    }
  };

  const cityLabel = city?.name || "this city";
  const isIndianCity = (city?.country_name || city?.country || "")
    .trim()
    .toLowerCase() === "india";

  const EventCard = ({ show }) => {
    const event = show.event;
    const venue = show.venue;
    const wishlistItems = useWishlist();
    const isLiked = wishlistItems.some((item) => String(item.id) === String(event?.id));

    return (
      <div
        onClick={() => event?.id && navigate(`/event/${event.slug || event.id}`)}
        className="flex flex-col group cursor-pointer"
      >
        <div className="relative aspect-[16/9] rounded-2xl overflow-hidden bg-gray-100">
          <img
            src={event?.poster_image_url || event?.banner_image_url || fallbackImage}
            alt={event?.title || "Event"}
            className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
          />

          {event?.is_featured && (
            <div className="absolute top-3 left-3 bg-black/60 backdrop-blur-sm text-white text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider">
              Featured
            </div>
          )}

          <button
            type="button"
            onClick={(clickEvent) => {
              clickEvent.stopPropagation();
              toggleWishlist(createWishlistItem({ event, show, venue }));
            }}
            className={`absolute bottom-3 right-3 bg-white p-2 rounded-full shadow-md hover:text-red-500 transition ${
              isLiked ? "text-red-500" : "text-gray-500"
            }`}
          >
            <Heart size={16} fill={isLiked ? "currentColor" : "none"} />
          </button>
        </div>

        <div className="mt-4 space-y-1">
          <p className="text-xs font-semibold text-gray-400 uppercase tracking-tight">
            {formatDate(show)}
          </p>

          <h3 className="text-[15px] font-bold text-gray-900 leading-snug line-clamp-2">
            {event?.title}
          </h3>

          <p className="text-sm text-gray-500 line-clamp-2">
            {event?.description || event?.category || "Live event"}
          </p>

          <p className="text-sm text-gray-400 font-medium truncate">
            {venue?.name && venue?.city
              ? `${venue.name}, ${venue.city}`
              : venue?.name || venue?.city || cityLabel}
          </p>

          <div className="flex items-center justify-between pt-3 border-t border-gray-50 mt-2">
            <span className="text-[12px] font-medium text-gray-500">
              {80 + (((event?.id || show.id) * 37) % 950)}+ Interested
            </span>

            <div className="flex items-center gap-1 text-gray-900">
              <Ticket size={14} className="text-gray-400" />
              <span className="text-xs font-bold">{formatPrice(show)}</span>
            </div>
          </div>
        </div>
      </div>
    );
  };

  return (
    <section className="bg-[#f5f5f5] min-h-screen pb-10 mt-20">
      <div
        className={`w-full text-white py-10 md:py-14 px-4 md:px-10 relative overflow-hidden ${
          isIndianCity
            ? "bg-gradient-to-r from-[#2f6ea5] via-[#5b86b3] to-[#ff8a3d]"
            : "bg-[#121212]"
        }`}
      >
        {!isIndianCity && (
          <>
            <div className="absolute inset-0 bg-[radial-gradient(circle_at_18%_20%,rgba(255,107,0,0.28),transparent_30%),radial-gradient(circle_at_82%_15%,rgba(14,165,233,0.28),transparent_32%),linear-gradient(135deg,#121212_0%,#1f2937_52%,#0f172a_100%)]" />
            <div className="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-black/40 to-transparent" />
          </>
        )}
        <div className="max-w-[1200px] mx-auto flex items-center relative z-10">
          <div className="max-w-xl">
            {!isIndianCity && city?.country_name && (
              <p className="text-xs font-bold uppercase tracking-[0.25em] text-orange-300 mb-3">
                International Events
              </p>
            )}
            <h1 className="text-3xl md:text-5xl font-semibold mb-4">
              All Events in <span className="font-bold capitalize">{cityLabel}</span>
            </h1>

            <p className="text-sm md:text-base text-white/90 leading-relaxed">
              Discover live events, festivals, shows, and experiences in{" "}
              {cityLabel}
              {city?.country_name ? `, ${city.country_name}` : ""}.
            </p>

            <button className="mt-6 bg-white text-[#ff6b00] px-5 py-2 rounded-full flex items-center gap-2 text-sm font-medium">
              Join the community
              <span className="bg-[#ff6b00] text-white w-5 h-5 flex items-center justify-center rounded-full text-xs">
                +
              </span>
            </button>
          </div>
        </div>

        {isIndianCity ? (
          <div className="absolute -right-2 bottom-0 h-full hidden md:block">
          <img src="/banner.png" className="h-[115%] object-contain" />
          </div>
        ) : (
          <div className="absolute right-8 bottom-8 hidden md:grid grid-cols-2 gap-3 w-[310px] rotate-[-3deg]">
            {["Concerts", "Festivals", "Workshops", "Nightlife"].map((label) => (
              <div
                key={label}
                className="rounded-2xl border border-white/15 bg-white/10 backdrop-blur-md p-4 shadow-xl"
              >
                <p className="text-sm font-bold">{label}</p>
                <p className="text-xs text-white/60 mt-1">Global picks</p>
              </div>
            ))}
          </div>
        )}
      </div>

      <div className="max-w-7xl mx-auto px-4 md:px-10 mt-10">
        <h2 className="text-2xl font-bold mb-6">Events in {cityLabel}</h2>

        {loading && <p className="text-gray-500 font-medium">Loading events...</p>}
        {error && !loading && <p className="text-red-600 font-medium">{error}</p>}
        {!loading && !error && shows.length === 0 && (
          <p className="text-gray-500 font-medium">No events found in {cityLabel}.</p>
        )}

        {!loading && !error && shows.length > 0 && (
          <>
            <div className="md:hidden overflow-x-auto pb-4 scrollbar-hide">
              <div className="inline-flex gap-6">
                {shows.map((show) => (
                  <div
                    key={show.id}
                    className="w-[75vw] max-w-[280px] flex-shrink-0"
                  >
                    <EventCard show={show} />
                  </div>
                ))}
              </div>
            </div>

            <div className="hidden md:grid md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-12">
              {shows.map((show) => (
                <EventCard key={show.id} show={show} />
              ))}
            </div>

            {hasMore && (
              <div className="mt-12 flex justify-center">
                <button
                  type="button"
                  onClick={handleSeeMore}
                  disabled={loadingMore}
                  className="rounded-full bg-[#ff6b00] px-8 py-3 text-sm font-bold text-white shadow-lg shadow-orange-200 transition hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-70"
                >
                  {loadingMore ? "Loading..." : "See More"}
                </button>
              </div>
            )}
          </>
        )}
      </div>

      <style>{`
        .scrollbar-hide::-webkit-scrollbar {
          display: none;
        }
      `}</style>
    </section>
  );
};

export default CityPage;
