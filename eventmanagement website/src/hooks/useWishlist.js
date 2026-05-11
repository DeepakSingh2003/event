import { useEffect, useState } from "react";
import { getWishlist, WISHLIST_UPDATED_EVENT } from "../utils/wishlist";

const useWishlist = () => {
  const [items, setItems] = useState(() => getWishlist());

  useEffect(() => {
    const syncWishlist = () => setItems(getWishlist());

    window.addEventListener(WISHLIST_UPDATED_EVENT, syncWishlist);
    window.addEventListener("storage", syncWishlist);

    return () => {
      window.removeEventListener(WISHLIST_UPDATED_EVENT, syncWishlist);
      window.removeEventListener("storage", syncWishlist);
    };
  }, []);

  return items;
};

export default useWishlist;
