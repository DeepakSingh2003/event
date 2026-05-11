import { Link } from "react-router-dom";

const roles = ["Frontend Engineer", "Backend Engineer", "Customer Success", "Event Partnerships"];

const CareersPage = () => (
  <main className="min-h-screen bg-white mt-20 text-gray-900">
    <section className="bg-[#f7f8fb] px-4 py-20 md:py-28">
      <div className="mx-auto max-w-7xl">
        <p className="text-sm font-bold uppercase tracking-[0.24em] text-[#ff6b00]">
          Careers
        </p>
        <h1 className="mt-5 max-w-4xl text-5xl font-black leading-tight md:text-7xl">
          Join the team building better event discovery
        </h1>
        <p className="mt-6 max-w-2xl text-lg leading-8 text-gray-600">
          We are looking for people who care about product details, local
          communities, and helping people spend more time at real experiences.
        </p>
      </div>
    </section>

    <section className="px-4 py-20">
      <div className="mx-auto max-w-7xl">
        <h2 className="text-4xl font-black">Open roles</h2>
        <div className="mt-8 divide-y divide-gray-200 border-y border-gray-200">
          {roles.map((role) => (
            <div key={role} className="flex flex-col gap-3 py-6 md:flex-row md:items-center md:justify-between">
              <div>
                <h3 className="text-xl font-black">{role}</h3>
                <p className="mt-1 text-sm text-gray-500">Openings coming soon</p>
              </div>
              <span className="text-sm font-bold text-[#ff6b00]">Coming soon</span>
            </div>
          ))}
        </div>
        <Link
          to="/contact"
          className="mt-10 inline-flex rounded-full bg-[#ff6b00] px-6 py-3 text-sm font-bold text-white"
        >
          Contact Team
        </Link>
      </div>
    </section>
  </main>
);

export default CareersPage;
