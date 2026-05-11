const WISHLIST_KEY = "event_wishlist";
export const WISHLIST_UPDATED_EVENT = "event-wishlist-updated";

export const getWishlist = () => {
  try {
    return JSON.parse(window.localStorage.getItem(WISHLIST_KEY) || "[]");
  } catch {
    return [];
  }
};

export const saveWishlist = (items) => {
  window.localStorage.setItem(WISHLIST_KEY, JSON.stringify(items));
  window.dispatchEvent(new Event(WISHLIST_UPDATED_EVENT));
};

export const isWishlisted = (eventId) =>
  getWishlist().some((item) => String(item.id) === String(eventId));

export const addToWishlist = (item) => {
  if (!item?.id) return getWishlist();

  const items = getWishlist();
  const exists = items.some((savedItem) => String(savedItem.id) === String(item.id));

  if (exists) return items;

  const updatedItems = [item, ...items];
  saveWishlist(updatedItems);
  return updatedItems;
};

export const removeFromWishlist = (eventId) => {
  const updatedItems = getWishlist().filter(
    (item) => String(item.id) !== String(eventId),
  );
  saveWishlist(updatedItems);
  return updatedItems;
};

export const toggleWishlist = (item) => {
  if (!item?.id) return getWishlist();

  return isWishlisted(item.id) ? removeFromWishlist(item.id) : addToWishlist(item);
};

export const formatWishlistDate = (show) => {
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

export const formatWishlistPrice = (show) => {
  if (show?.formatted_price) return show.formatted_price;
  if (!show?.price || Number(show.price) === 0) return "Free";

  return new Intl.NumberFormat("en-IN", {
    style: "currency",
    currency: show.currency_code || "INR",
    maximumFractionDigits: 0,
  }).format(Number(show.price));
};

export const createWishlistItem = ({ event, show, venue, image, date, price, location }) => {
  if (!event?.id) return null;

  const resolvedVenue = venue || show?.venue;

  return {
    id: event.id,
    title: event.title,
    slug: event.slug,
    category: event.category || "Event",
    image:
      image ||
      event.poster_image_url ||
      event.banner_image_url ||
      "https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&q=80&w=600",
    date: date || formatWishlistDate(show),
    price: price || formatWishlistPrice(show),
    location:
      location ||
      (resolvedVenue?.name && resolvedVenue?.city
        ? `${resolvedVenue.name}, ${resolvedVenue.city}`
        : resolvedVenue?.name || resolvedVenue?.city || "Location coming soon"),
  };
};
