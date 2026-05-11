import { Link } from "react-router-dom";

const stats = [
  ["2M+", "Monthly explorers"],
  ["500+", "Cities covered"],
  ["10K+", "Events listed"],
  ["50K+", "Tickets booked"],
];

const values = [
  ["Stay happening", "We help people go out, meet others, and create better memories."],
  ["Local discovery", "Every city deserves one place to find what is happening nearby."],
  ["Simple booking", "Clear event details, easy ticket selection, and secure checkout."],
  ["Community first", "We support explorers, organizers, venues, and local communities."],
];

const AboutPage = () => (
  <main className="min-h-screen bg-white mt-20 text-gray-900">
    <section className="bg-[#f7f8fb] px-4 py-20 md:py-28">
      <div className="mx-auto grid max-w-7xl gap-12 md:grid-cols-[1.05fr_0.95fr] md:items-center">
        <div>
          <p className="text-sm font-bold uppercase tracking-[0.24em] text-[#ff6b00]">
            About MovieBooky
          </p>
          <h1 className="mt-5 text-5xl font-black leading-tight md:text-7xl">
            Making every city more happening
          </h1>
          <p className="mt-6 max-w-2xl text-lg leading-8 text-gray-600">
            MovieBooky helps people discover events, reserve seats, and book
            tickets without friction. We bring live experiences, local shows,
            festivals, workshops, and entertainment together in one place.
          </p>
          <div className="mt-8 flex flex-wrap gap-3">
            <Link
              to="/search"
              className="rounded-full bg-[#ff6b00] px-6 py-3 text-sm font-bold text-white"
            >
              Discover Events
            </Link>
            <Link
              to="/contact"
              className="rounded-full border border-gray-300 px-6 py-3 text-sm font-bold text-gray-900"
            >
              Partner With Us
            </Link>
          </div>
        </div>
        <div className="min-h-[360px] overflow-hidden rounded-[28px]">
          <img
            src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&q=80&w=1200"
            alt="People enjoying an event"
            className="h-full w-full object-cover"
          />
        </div>
      </div>
    </section>

    <section className="px-4 py-14">
      <div className="mx-auto grid max-w-7xl gap-6 sm:grid-cols-2 lg:grid-cols-4">
        {stats.map(([number, label]) => (
          <div key={label} className="border-l-4 border-[#ff6b00] pl-5">
            <p className="text-4xl font-black">{number}</p>
            <p className="mt-1 text-sm font-semibold text-gray-500">{label}</p>
          </div>
        ))}
      </div>
    </section>

    <section className="bg-[#111827] px-4 py-20 text-white">
      <div className="mx-auto grid max-w-7xl gap-12 md:grid-cols-2">
        <div>
          <p className="text-sm font-bold uppercase tracking-[0.22em] text-orange-300">
            Our mission
          </p>
          <h2 className="mt-4 text-4xl font-black">Simplify event discovery</h2>
          <p className="mt-5 text-lg leading-8 text-white/70">
            We want people to find experiences they love, wherever they are,
            and book tickets with confidence.
          </p>
        </div>
        <div>
          <p className="text-sm font-bold uppercase tracking-[0.22em] text-orange-300">
            Our vision
          </p>
          <h2 className="mt-4 text-4xl font-black">A more connected world</h2>
          <p className="mt-5 text-lg leading-8 text-white/70">
            We imagine cities where people spend less time searching and more
            time showing up, meeting people, and making memories.
          </p>
        </div>
      </div>
    </section>

    <section className="px-4 py-20">
      <div className="mx-auto max-w-7xl">
        <p className="text-sm font-bold uppercase tracking-[0.22em] text-[#ff6b00]">
          Values
        </p>
        <h2 className="mt-4 text-4xl font-black">What drives us</h2>
        <div className="mt-10 grid gap-6 md:grid-cols-2">
          {values.map(([title, text]) => (
            <div key={title} className="border-t border-gray-200 pt-6">
              <h3 className="text-xl font-black">{title}</h3>
              <p className="mt-3 leading-7 text-gray-600">{text}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  </main>
);

export default AboutPage;
