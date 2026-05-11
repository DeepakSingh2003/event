import React, { useEffect, useRef } from "react";
// Data extracted from your provided images
const eventMemories = [
  {
    id: 1,
    type: "image",
    user: "Arpita Biswas",
    time: "4 days ago",
    img: "https://cdn2.allevents.in/transup/a8/b47943f3184ba7a39f1adbe8fe1493/images/PXL_20260419_064206101.webp",
    size: "tall",
  },
  {
    id: 2,
    type: "review",
    user: "Heather Raymond",
    time: "2 days ago",
    review: "Excellent event!...",
    stars: 5,
    size: "short",
  },
  {
    id: 3,
    type: "image",
    user: "Rima Shah",
    time: "5 days ago",
    img: "https://cdn2.allevents.in/transup/8f/e9f14df2d94548b662259a3bb5eb59/images/Screenshot_20260420_065839_Photos.webp",
    size: "short",
  },
  {
    id: 4,
    type: "image",
    user: "Sagar Butake",
    time: "4 days ago",
    img: "https://cdn2.allevents.in/transup/83/ef0a0fbace44bca370b294c99d717f/images/IMG_20260418_182046.webp",
    size: "tall",
  },
  {
    id: 5,
    type: "review",
    user: "Naomi Blackmore",
    time: "2 days ago",
    review: "Learnt a lot about my responsibilities & rights...",
    stars: 5,
    size: "tall",
  },
  {
    id: 6,
    type: "image",
    user: "Nia Lacayo",
    time: "4 days ago",
    img: "https://cdn2.allevents.in/transup/33/4c0a3382a449fa84d60077e4bc83e7/images/threema-20260411-172713553.webp",
    size: "short",
  },
  {
    id: 7,
    type: "image",
    user: "Yonathan Cornelio",
    time: "5 days ago",
    img: "https://cdn2.allevents.in/transup/9b/b82b80fc0043d784ecf9f87d2ffeb4/images/image_picker_17EBFE3C-B437-4E85-8B42-9A7935AFF37B-19481-00000802494CA781.webp",
    size: "short",
  },
  {
    id: 8,
    type: "image",
    user: "Marcella Wolkowitz",
    time: "5 days ago",
    img: "https://cdn2.allevents.in/transup/f9/3adbf631124c64950560bdfb952e50/images/image_picker_6FD1E4EC-422C-4B2D-9F00-4655950AFFB0-1193-0000005CF7842FB5.webp",
    size: "tall",
  },
  {
    id: 9,
    type: "review",
    user: "Dharmik Kariya",
    time: "2 days ago",
    review: "Best car show ever in Rajkot...",
    stars: 5,
    size: "short",
  },
  {
    id: 10,
    type: "image",
    user: "Zeeshan Khan",
    time: "5 days ago",
    img: "https://cdn2.allevents.in/transup/a6/b16ccf4a624f97a83c16b8796cf881/images/image_picker_86AB7BBA-3D1A-4143-9C8C-B2D2DE301A53-44266-00000390FFD11649.webp",
    size: "tall",
  },
];
const MemoriesWall = () => {
  const scrollRef = useRef(null);

  const columns = [];

  for (let i = 0; i < eventMemories.length; i += 2) {
    columns.push(eventMemories.slice(i, i + 2));
  }

  // Auto infinite scroll
  useEffect(() => {
    const container = scrollRef.current;
    let scrollAmount = 0;

    const autoScroll = () => {
      scrollAmount += 0.3; // speed
      if (container) {
        container.scrollLeft = scrollAmount;

        // infinite loop
        if (scrollAmount >= container.scrollWidth / 2) {
          scrollAmount = 0;
        }
      }
    };

    const interval = setInterval(autoScroll, 16);
    return () => clearInterval(interval);
  }, []);

  return (
    <div className="bg-[#FEF9F8] py-16 overflow-hidden">
      {/* Header */}
      <div className="text-center mb-12">
        <h1 className="text-3xl md:text-4xl font-bold text-slate-800">
          Join Thousands Booking Events Every Day
        </h1>
        <p className="text-slate-500 mt-2">
          Join the people turning moments into memories.
        </p>
      </div>

      {/* Moving Container */}
      <div
        ref={scrollRef}
        className="flex gap-6 overflow-x-hidden whitespace-nowrap"
      >
        {[...columns, ...columns].map((column, colIdx) => (
          <div key={colIdx} className="flex flex-col gap-6 min-w-[250px]">
            {column.map((item) => (
              <div
                key={item.id + colIdx}
                className={`rounded-2xl overflow-hidden shadow-sm bg-white
                ${item.size === "tall" ? "h-[320px]" : "h-[200px]"}`}
              >
                {item.type === "image" ? (
                  <div className="relative h-full">
                    <img
                      src={item.img}
                      alt={item.user}
                      className="w-full h-full object-cover"
                    />

                    {/* overlay */}
                    <div className="absolute bottom-0 w-full p-3 bg-gradient-to-t from-black/70 to-transparent text-white">
                      <p className="text-sm font-semibold">{item.user}</p>
                      <p className="text-xs opacity-80">{item.time}</p>
                    </div>
                  </div>
                ) : (
                  <div className="h-full flex flex-col justify-between p-4 text-center">
                    <div className="text-yellow-400">
                      {"★".repeat(item.stars)}
                    </div>
                    <p className="text-sm italic">"{item.review}"</p>
                    <p className="text-xs font-semibold">{item.user}</p>
                  </div>
                )}
              </div>
            ))}
          </div>
        ))}
      </div>
    </div>
  );
};

export default MemoriesWall;
