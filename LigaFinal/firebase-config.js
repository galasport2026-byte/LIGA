// ================================================
// 🔥 CONFIGURACIÓN DE FIREBASE
// ================================================
// 1. Ve a https://console.firebase.google.com
// 2. Crea un proyecto nuevo (gratis)
// 3. Ve a "Firestore Database" → Crear base de datos → Modo de producción
// 4. Ve a Configuración del proyecto ⚙️ → Agrega una app web (</>)
// 5. Copia y pega los valores que te da Firebase aquí abajo
// ================================================

const firebaseConfig = {
    apiKey:            "TU_API_KEY_AQUI",
    authDomain:        "TU_PROYECTO.firebaseapp.com",
    projectId:         "TU_PROYECTO_ID",
    storageBucket:     "TU_PROYECTO.appspot.com",
    messagingSenderId: "TU_SENDER_ID",
    appId:             "TU_APP_ID"
};

// ================================================
// NO MODIFIQUES NADA DEBAJO DE ESTA LÍNEA
// ================================================
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
import { getFirestore, collection, doc, getDocs, setDoc, deleteDoc, onSnapshot, getDoc }
    from "https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore.js";

const app = initializeApp(firebaseConfig);
const db  = getFirestore(app);

export { db, collection, doc, getDocs, setDoc, deleteDoc, onSnapshot, getDoc };
