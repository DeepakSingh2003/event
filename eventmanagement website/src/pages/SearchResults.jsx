import { useCallback, useEffect, useMemo, useState } from "react";
import { useNavigate, useSearchParams } from "react-router-dom";
import { ChevronRight, Heart, Home, Ticket } from "lucide-react";
import useWishlist from "../hooks/useWishlist";
import { createWishlistItem, toggleWishlist } from "../utils/wishlist";

const API_BASE_URL = import.meta.env.VITE_API_URL || "http://localhost:8000";

const fallbackImage =
  "https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&q=80&w=900";

const bannerImages = {
  music:
    "https://images.unsplash.com/photo-1501386761578-eac5c94b800a?auto=format&fit=crop&q=80&w=1800",
  concerts:
    "https://images.unsplash.com/photo-1501386761578-eac5c94b800a?auto=format&fit=crop&q=80&w=1800",
  comedy:
    "https://images.unsplash.com/photo-1527224538127-2104bb71c51b?auto=format&fit=crop&q=80&w=1800",
  food: "https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&q=80&w=1800",
  business:
    "https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&q=80&w=1800",
  technology:
    "https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&q=80&w=1800",
  cultural:
    "https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?q=80&w=1800",
  festivals:
    "https://images.unsplash.com/photo-1472653431158-6364773b2a56?auto=format&fit=crop&q=80&w=1800",
};

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

const titleCase = (value) =>
  value
    .split(" ")
    .filter(Boolean)
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(" ");

const getInterestedCount = (eventId) => {
  const count = 120 + (((Number(eventId) || 1) * 43) % 990);

  if (count >= 1000) return `${(count / 1000).toFixed(1)}k+`;

  return `${count}+`;
};

const SearchResults = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const [events, setEvents] = useState([]);
  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [error, setError] = useState("");
  const [currentPage, setCurrentPage] = useState(1);
  const [hasMore, setHasMore] = useState(false);

  const label = searchParams.get("label");
  const query = searchParams.get("q");
  const eventId = searchParams.get("event_id");
  const categoryId = searchParams.get("category_id");
  const cityId = searchParams.get("city_id");
  const countryId = searchParams.get("country_id");

  const categoryName = useMemo(() => {
    const rawName = label || query || "Events";
    return titleCase(rawName.replace(/\s+events$/i, "").trim() || "Events");
  }, [label, query]);

  const pageTitle = useMemo(() => {
    if (eventId) return "Selected Event";
    if (categoryId) return `${categoryName} Events`;
    if (label) return `${categoryName} Events`;
    if (query) return `Search results for ${query}`;
    return "All Events";
  }, [categoryId, categoryName, eventId, label, query]);

  const heroImage = useMemo(() => {
    const categoryKey = categoryName.toLowerCase();
    const matchedKey = Object.keys(bannerImages).find((key) =>
      categoryKey.includes(key),
    );

    return (
      bannerImages[matchedKey] ||
      events[0]?.banner_image_url ||
      events[0]?.poster_image_url ||
      fallbackImage
    );
  }, [categoryName, events]);

  const buildEventParams = useCallback((page = 1) => {
    const params = new URLSearchParams();
    if (query) params.set("search", query);
    if (categoryId) params.set("category_id", categoryId);
    if (cityId) params.set("city_id", cityId);
    if (countryId) params.set("country_id", countryId);
    params.set("page", page);

    return params;
  }, [categoryId, cityId, countryId, query]);

  const updatePagination = useCallback((result, page) => {
    setCurrentPage(page);
    setHasMore(Boolean(result.links?.next || result.meta?.current_page < result.meta?.last_page));
  }, []);

  useEffect(() => {
    const fetchResults = async () => {
      setLoading(true);
      setError("");
      setHasMore(false);
      setCurrentPage(1);

      try {
        if (eventId) {
          const response = await fetch(
            `${API_BASE_URL}/api/events/${eventId}`,
            {
              headers: { Accept: "application/json" },
            },
          );

          if (!response.ok) throw new Error("Failed to load event");

          const result = await response.json();
          setEvents(result.data ? [result.data] : []);
          setHasMore(false);
          return;
        }

        const params = buildEventParams(1);

        const response = await fetch(
          `${API_BASE_URL}/api/events?${params.toString()}`,
          {
            headers: { Accept: "application/json" },
          },
        );

        if (!response.ok) throw new Error("Failed to load results");

        const result = await response.json();
        setEvents(Array.isArray(result.data) ? result.data : []);
        updatePagination(result, 1);
      } catch (fetchError) {
        setError(fetchError.message || "Something went wrong");
      } finally {
        setLoading(false);
      }
    };

    fetchResults();
  }, [query, eventId, categoryId, cityId, countryId, buildEventParams, updatePagination]);

  const handleSeeMore = async () => {
    if (loadingMore || !hasMore) return;

    const nextPage = currentPage + 1;
    setLoadingMore(true);
    setError("");

    try {
      const params = buildEventParams(nextPage);
      const response = await fetch(
        `${API_BASE_URL}/api/events?${params.toString()}`,
        {
          headers: { Accept: "application/json" },
        },
      );

      if (!response.ok) throw new Error("Failed to load more events");

      const result = await response.json();
      setEvents((existingEvents) => [
        ...existingEvents,
        ...(Array.isArray(result.data) ? result.data : []),
      ]);
      updatePagination(result, nextPage);
    } catch (fetchError) {
      setError(fetchError.message || "Something went wrong");
    } finally {
      setLoadingMore(false);
    }
  };

  return (
    <section className="bg-[#f6f6f6] min-h-screen mt-20 pb-12">
      <div className="px-4 pt-6 md:px-8">
        <div className="relative mx-auto h-[338px] max-h-[338px] w-full max-w-[1234px] overflow-hidden rounded-[26px] bg-[#130934] text-white">
          <img
            src={heroImage}
            alt={pageTitle}
            className="absolute inset-0 h-full w-full object-cover"
          />
          <div className="absolute inset-0 bg-gradient-to-r from-[#17064a] via-[#170b2f]/90 to-black/20" />
          <div className="absolute inset-0 bg-gradient-to-t from-black/45 via-transparent to-black/10" />

          <div className="relative z-10 flex h-full flex-col justify-between px-5 py-6 sm:px-8 md:px-14 md:py-10">
            <div>
              <div className="mb-7 flex items-center gap-2 text-sm font-semibold text-white/70 md:text-base">
                <Home size={16} />
                <ChevronRight size={16} />
                <span>
                  {cityId || countryId ? "Selected Location" : "Events"}
                </span>
                <ChevronRight size={16} />
                <span className="text-white">{pageTitle}</span>
              </div>

              <div className="max-w-3xl">
                <h1 className="text-3xl font-extrabold leading-tight md:text-5xl">
                  {pageTitle}
                </h1>
                <p className="mt-1 text-xl font-bold text-white md:text-2xl">
                  {cityId || countryId ? "near you" : "for you"}
                </p>
                <p className="mt-5 max-w-3xl text-sm font-medium leading-7 text-white/70 md:text-base">
                  Explore handpicked {categoryName.toLowerCase()} shows, live
                  experiences, and upcoming events. Choose an event below and
                  book your tickets in a few simple steps.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div className="mx-auto mt-10 max-w-7xl px-4 md:px-8">
        <div className="mb-8 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <p className="text-sm font-bold uppercase tracking-[0.2em] text-[#ff6b00]">
              Category Shows
            </p>
            <h2 className="mt-2 text-2xl font-extrabold text-gray-900 md:text-3xl">
              {pageTitle}
            </h2>
          </div>

          {!loading && !error && (
            <p className="text-sm font-semibold text-gray-500">
              {events.length} {events.length === 1 ? "event" : "events"} found
            </p>
          )}
        </div>

        {loading && (
          <p className="text-gray-500 font-medium">Loading events...</p>
        )}
        {error && !loading && (
          <p className="text-red-600 font-medium">{error}</p>
        )}
        {!loading && !error && events.length === 0 && (
          <p className="text-gray-500 font-medium">No events found.</p>
        )}

        {!loading && !error && events.length > 0 && (
          <>
            <div className="md:hidden overflow-x-auto pb-4 scrollbar-hide">
              <div className="inline-flex gap-6">
                {events.map((event) => (
                  <div
                    key={event.id}
                    className="w-[76vw] max-w-[290px] flex-shrink-0"
                  >
                    <EventCard event={event} navigate={navigate} />
                  </div>
                ))}
              </div>
            </div>

            <div className="hidden md:grid md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-12">
              {events.map((event) => (
                <EventCard key={event.id} event={event} navigate={navigate} />
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
        .scrollbar-hide {
          -ms-overflow-style: none;
          scrollbar-width: none;
        }
      `}</style>
    </section>
  );
};

const EventCard = ({ event, navigate }) => {
  const show = event.primary_listing;
  const venue = show?.venue;
  const wishlistItems = useWishlist();
  const isLiked = wishlistItems.some((item) => String(item.id) === String(event.id));

  return (
    <div
      onClick={() => navigate(`/event/${event.slug || event.id}`)}
      className="group flex cursor-pointer flex-col"
    >
      <div className="relative aspect-[16/9] overflow-hidden rounded-2xl bg-gray-100">
        <img
          src={
            event.poster_image_url || event.banner_image_url || fallbackImage
          }
          alt={event.title}
          loading="lazy"
          className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
        />

        {event.is_featured && (
          <div className="absolute left-3 top-3 rounded bg-black/60 px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur-sm">
            Featured
          </div>
        )}

        <button
          type="button"
          onClick={(clickEvent) => {
            clickEvent.stopPropagation();
            toggleWishlist(createWishlistItem({ event, show, venue }));
          }}
          className={`absolute bottom-3 right-3 rounded-full bg-white p-2 shadow-md transition hover:text-red-500 ${
            isLiked ? "text-red-500" : "text-gray-500"
          }`}
        >
          <Heart size={16} fill={isLiked ? "currentColor" : "none"} />
        </button>
      </div>

      <div className="mt-4 space-y-1">
        <p className="text-xs font-semibold uppercase tracking-tight text-gray-400">
          {formatDate(show)}
        </p>
        <h3 className="h-11 text-[15px] font-bold leading-snug text-gray-900 line-clamp-2">
          {event.title}
        </h3>
        <p className="truncate text-sm font-medium text-gray-400">
          {venue?.name && venue?.city
            ? `${venue.name}, ${venue.city}`
            : venue?.name || venue?.city || event.category || "Event"}
        </p>

        <div className="mt-2 flex items-center justify-between border-t border-gray-100 pt-3">
          <span className="text-[12px] font-medium text-gray-500">
            {getInterestedCount(event.id)} Interested
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

export default SearchResults;
