const updates = ["Booking confirmations", "Event reminders", "Payment alerts", "Organizer updates"];

const NotificationsPage = () => (
  <main className="min-h-screen bg-white mt-20 text-gray-900">
    <section className="bg-[#f7f8fb] px-4 py-20 md:py-28">
      <div className="mx-auto max-w-7xl">
        <p className="text-sm font-bold uppercase tracking-[0.24em] text-[#ff6b00]">
          Account
        </p>
        <h1 className="mt-5 text-5xl font-black md:text-7xl">Notifications</h1>
        <p className="mt-6 max-w-2xl text-lg leading-8 text-gray-600">
          Important booking, event, and payment updates will be organized here.
        </p>
      </div>
    </section>

    <section className="px-4 py-16">
      <div className="mx-auto max-w-5xl divide-y divide-gray-200 border-y border-gray-200">
        {updates.map((item) => (
          <div key={item} className="py-7">
            <h2 className="text-2xl font-black">{item}</h2>
            <p className="mt-2 text-gray-500">No new notifications.</p>
          </div>
        ))}
      </div>
    </section>
  </main>
);

export default NotificationsPage;
