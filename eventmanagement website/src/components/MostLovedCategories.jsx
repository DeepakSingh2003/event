import { useEffect, useState } from "react";
import { ChevronRight, X } from "lucide-react";
import * as LucideIcons from "lucide-react";
import { Link } from "react-router-dom";

const API_BASE_URL = import.meta.env.VITE_API_URL || "http://localhost:8000";

const MostLovedCategories = () => {
  const [categories, setCategories] = useState([]);
  const [isModalOpen, setIsModalOpen] = useState(false);

  const getCategorySearchPath = (category) =>
    `/search?category_id=${encodeURIComponent(category.id)}&label=${encodeURIComponent(category.name)}`;

  useEffect(() => {
    const fetchCategories = async () => {
      try {
        const res = await fetch(`${API_BASE_URL}/api/categories`, {
          method: "GET",
          headers: {
            Accept: "application/json",
          },
        });

        if (!res.ok) {
          console.log("Categories API status:", res.status);
          return;
        }

        const data = await res.json();

        const categoryList = Array.isArray(data)
          ? data
          : Array.isArray(data.data)
            ? data.data
            : [];

        const normalizedCategories = categoryList.map((cat) => ({
          id: cat.id ?? cat.slug ?? cat.name,
          name: cat.name,
          slug: cat.slug,
          icon: cat.icon,
          bgColor: "bg-orange-50",
          accentColor: "bg-orange-200",
          textColor: "text-[#ff6b00]",
        }));

        setCategories(normalizedCategories);
      } catch (error) {
        console.error("Error fetching categories:", error);
      }
    };

    fetchCategories();
  }, []);

  const renderIcon = (iconName, size = 42) => {
    const IconComponent =
      LucideIcons[iconName] || LucideIcons.Shapes || LucideIcons.Circle;

    return <IconComponent size={size} />;
  };

  const visibleCategories = categories.slice(0, 7);

  return (
    <section className="py-10 bg-white">
      <div className="max-w-[1170px] mx-auto px-4">
        <div className="flex items-center justify-between mb-10">
          <h2 className="text-[20px] md:text-[28px] font-bold text-gray-900">
            Most-Loved Categories
          </h2>

          <button
            type="button"
            onClick={() => setIsModalOpen(true)}
            className="flex items-center gap-1 text-[#ff6b00] font-semibold hover:underline"
          >
            View all <ChevronRight size={18} />
          </button>
        </div>

        <div
          className="grid grid-flow-col auto-cols-[minmax(170px,220px)] gap-4 overflow-x-auto pb-4 md:grid-flow-row md:grid md:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 md:gap-x-4 md:gap-y-14 md:overflow-visible snap-x snap-mandatory scrollbar-hide"
          style={{ gridTemplateRows: "repeat(2, auto)" }}
        >
          {visibleCategories.map((cat, index) => (
            <Link
              key={cat.id || index}
              to={getCategorySearchPath(cat)}
              className={`relative h-[100px] rounded-2xl flex items-center transition-all duration-300 hover:shadow-xl group overflow-hidden snap-start ${cat.bgColor}`}
            >
              <span className="relative z-10 pl-5 text-sm md:text-[16px] font-bold text-gray-800 w-1/2">
                {cat.name}
              </span>

              <div
                className={`absolute right-0 top-0 h-full w-[45%] rounded-r-2xl opacity-40 ${cat.accentColor}`}
              ></div>

              <div className="absolute right-0 top-0 h-full w-1/2 flex items-center justify-center z-20 pointer-events-none">
                <div className="text-[#ff6b00] drop-shadow-md transition-transform duration-300 group-hover:scale-110 group-hover:-translate-y-2">
                  {renderIcon(cat.icon)}
                </div>
              </div>
            </Link>
          ))}

          <button
            type="button"
            onClick={() => setIsModalOpen(true)}
            className="relative h-[100px] rounded-2xl flex flex-col items-center justify-center bg-orange-50 border-2 border-dashed border-orange-200 hover:bg-orange-100 cursor-pointer transition snap-start"
          >
            <span className="text-[10px] text-orange-500 font-bold uppercase tracking-widest">
              Explore All
            </span>
            <span className="text-sm md:text-[16px] font-semibold text-[#ff6b00]">
              Categories
            </span>
          </button>
        </div>
      </div>

      {isModalOpen && (
        <div className="fixed inset-0 z-[90] flex items-center justify-center bg-black/60 px-4">
          <div className="w-full max-w-3xl rounded-2xl bg-white shadow-2xl">
            <div className="flex items-center justify-between border-b border-gray-100 px-5 py-4">
              <div>
                <h3 className="text-xl font-bold text-gray-900">
                  Explore All Categories
                </h3>
                <p className="text-sm text-gray-500">
                  Choose a category to view matching events.
                </p>
              </div>
              <button
                type="button"
                onClick={() => setIsModalOpen(false)}
                className="rounded-full p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-900"
              >
                <X size={22} />
              </button>
            </div>

            <div className="max-h-[70vh] overflow-y-auto p-5">
              <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
                {categories.map((cat, index) => (
                  <Link
                    key={cat.id || index}
                    to={getCategorySearchPath(cat)}
                    onClick={() => setIsModalOpen(false)}
                    className="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50 p-4 text-left transition hover:border-orange-200 hover:bg-orange-50"
                  >
                    <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-white text-[#ff6b00] shadow-sm">
                      {renderIcon(cat.icon, 24)}
                    </span>
                    <span className="font-bold text-gray-800">{cat.name}</span>
                  </Link>
                ))}
              </div>
            </div>
          </div>
        </div>
      )}

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

export default MostLovedCategories;
