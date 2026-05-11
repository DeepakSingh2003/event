import { initializeApp } from "firebase/app";
import { getAuth, GoogleAuthProvider } from "firebase/auth";

// Your Firebase config (already correct ✅)
const firebaseConfig = {
  apiKey: "AIzaSyDMSjk5WDFSfd_YoEov2Rbf0L3OH8T5L7Y",
  authDomain: "moviesbooky-eebd7.firebaseapp.com",
  projectId: "moviesbooky-eebd7",
  appId: "1:57113778232:web:9bfffcee253c6d36376ecd",
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);

// ✅ Auth setup (THIS is important)
export const auth = getAuth(app);
export const googleProvider = new GoogleAuthProvider();
