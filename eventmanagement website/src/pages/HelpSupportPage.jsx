import { Link } from "react-router-dom";

const topics = [
  ["Booking help", "Find tickets, booking ID, and order status."],
  ["Payment support", "Resolve failed payments or duplicate charges."],
  ["Refund questions", "Understand cancellation and refund policies."],
  ["Event support", "Get venue, timing, and entry information."],
];

const HelpSupportPage = () => (
  <main className="min-h-screen bg-white mt-20 text-gray-900">
    <section className="bg-[#111827] px-4 py-20 text-white md:py-28">
      <div className="mx-auto max-w-7xl">
        <p className="text-sm font-bold uppercase tracking-[0.24em] text-orange-300">
          Support
        </p>
        <h1 className="mt-5 text-5xl font-black md:text-7xl">Help & Support</h1>
        <p className="mt-6 max-w-2xl text-lg leading-8 text-white/70">
          Get help with bookings, payments, refunds, event information, and
          account access.
        </p>
      </div>
    </section>

    <section className="px-4 py-20">
      <div className="mx-auto grid max-w-7xl gap-8 md:grid-cols-2">
        {topics.map(([title, text]) => (
          <div key={title} className="border-t border-gray-200 pt-6">
            <h2 className="text-2xl font-black">{title}</h2>
            <p className="mt-3 leading-7 text-gray-600">{text}</p>
          </div>
        ))}
      </div>
      <div className="mx-auto mt-10 max-w-7xl">
        <Link to="/contact" className="inline-flex rounded-full bg-[#ff6b00] px-6 py-3 text-sm font-bold text-white">
          Contact Support
        </Link>
      </div>
    </section>
  </main>
);

export default HelpSupportPage;
