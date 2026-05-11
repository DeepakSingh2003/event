import { Heart, Ticket, Trash2 } from "lucide-react";
import { Link, useNavigate } from "react-router-dom";
import useWishlist from "../hooks/useWishlist";
import { removeFromWishlist } from "../utils/wishlist";

const WishlistPage = () => {
  const wishlist = useWishlist();
  const navigate = useNavigate();

  return (
    <main className="min-h-screen bg-white mt-20 text-gray-900">
      <section className="bg-[#111827] px-4 py-20 text-white md:py-28">
        <div className="mx-auto max-w-7xl">
          <p className="text-sm font-bold uppercase tracking-[0.24em] text-orange-300">
            Account
          </p>
          <h1 className="mt-5 text-5xl font-black md:text-7xl">Wishlist</h1>
          <p className="mt-6 max-w-2xl text-lg leading-8 text-white/70">
            Events you saved with the heart button appear here.
          </p>
        </div>
      </section>

      <section className="px-4 py-20">
        <div className="mx-auto max-w-7xl">
          {wishlist.length === 0 ? (
            <div className="border-y border-gray-200 py-14 text-center">
              <Heart size={42} className="mx-auto text-[#ff6b00]" />
              <h2 className="mt-5 text-3xl font-black">No saved events yet</h2>
              <p className="mx-auto mt-4 max-w-xl leading-7 text-gray-600">
                Tap the heart on any event card or event details page to save it.
              </p>
              <Link
                to="/search"
                className="mt-8 inline-flex rounded-full bg-[#ff6b00] px-6 py-3 text-sm font-bold text-white"
              >
                Explore Events
              </Link>
            </div>
          ) : (
            <>
              <div className="mb-8 flex items-end justify-between">
                <div>
                  <p className="text-sm font-bold uppercase tracking-[0.22em] text-[#ff6b00]">
                    Saved Events
                  </p>
                  <h2 className="mt-2 text-3xl font-black">
                    {wishlist.length} {wishlist.length === 1 ? "event" : "events"}
                  </h2>
                </div>
              </div>

              <div className="grid gap-x-6 gap-y-12 md:grid-cols-2 lg:grid-cols-4">
                {wishlist.map((event) => (
                  <div key={event.id} className="group flex flex-col">
                    <div
                      onClick={() => navigate(`/event/${event.slug || event.id}`)}
                      className="relative aspect-[16/9] cursor-pointer overflow-hidden rounded-2xl bg-gray-100"
                    >
                      <img
                        src={event.image}
                        alt={event.title}
                        className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                      />
                      <button
                        type="button"
                        onClick={(clickEvent) => {
                          clickEvent.stopPropagation();
                          removeFromWishlist(event.id);
                        }}
                        className="absolute bottom-3 right-3 rounded-full bg-white p-2 text-red-500 shadow-md transition hover:bg-red-50"
                        aria-label={`Remove ${event.title} from wishlist`}
                      >
                        <Trash2 size={16} />
                      </button>
                    </div>

                    <div className="mt-4 space-y-1">
                      <p className="text-xs font-semibold uppercase tracking-tight text-gray-400">
                        {event.date}
                      </p>
                      <h3 className="h-11 text-[15px] font-bold leading-snug text-gray-900 line-clamp-2">
                        {event.title}
                      </h3>
                      <p className="truncate text-sm font-medium text-gray-400">
                        {event.location}
                      </p>
                      <div className="mt-2 flex items-center gap-1 border-t border-gray-100 pt-3 text-gray-900">
                        <Ticket size={14} className="text-gray-400" />
                        <span className="text-xs font-bold">{event.price}</span>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </>
          )}
        </div>
      </section>
    </main>
  );
};

export default WishlistPage;
