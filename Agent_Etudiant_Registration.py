from tkinter import *
from tkinter import ttk, messagebox
import mysql.connector
import cv2
import os
import uuid
from PIL import Image, ImageTk
from datetime import datetime

COLOR_BORD = "#800000"
FONT_TITLE = ("Helvetica", 22, "bold")

def open_etudiant_registration():
    root = Toplevel()
    root.title("Enregistrement des Étudiants")
    root.geometry("600x700")
    root.configure(bg="white")

    Label(root, text="Formulaire d'enregistrement", font=FONT_TITLE, fg=COLOR_BORD, bg="white").pack(pady=20)

    id_etudiant_holder = {"value": None}

    def generer_matricule():
        return "ETD-" + uuid.uuid4().hex[:8].upper()

    matricule_var = StringVar(value=generer_matricule())

    def champ(label):
        Label(root, text=label, font=("Helvetica", 13), bg="white").pack()
        entry = Entry(root, font=("Helvetica", 13))
        entry.pack(pady=5, fill=X, padx=50)
        return entry

    def combo_champ(label, values):
        Label(root, text=label, font=("Helvetica", 13), bg="white").pack()
        combo = ttk.Combobox(root, values=values, font=("Helvetica", 13), state="readonly")
        combo.pack(pady=5, fill=X, padx=50)
        return combo

    nom_entry = champ("Nom complet")
    sexe_combo = combo_champ("Sexe", ["Masculin", "Féminin"])
    tel_entry = champ("Numéro de téléphone")
    Label(root, text="Matricule (auto)", font=("Helvetica", 13), bg="white").pack()
    Entry(root, textvariable=matricule_var, font=("Helvetica", 13), state="readonly").pack(pady=5, fill=X, padx=50)
    dep_entry = champ("Département")
    opt_entry = champ("Options")

    def capture_etudiant_photo():
        id_etudiant = id_etudiant_holder["value"]
        if not id_etudiant:
            messagebox.showerror("Erreur", "Aucun étudiant sélectionné pour la capture.")
            return

        folder = "photos_etudiants"
        os.makedirs(folder, exist_ok=True)

        window = Toplevel()
        window.title("Capture Visage - Étudiant")
        window.configure(bg="white")

        lmain = Label(window)
        lmain.pack()
        capture_ready = [False]

        def detect_camera_index():
            for i in range(4):
                cam = cv2.VideoCapture(i)
                if cam.isOpened():
                    ret, _ = cam.read()
                    cam.release()
                    if ret:
                        return i
            return -1

        camera_index = detect_camera_index()
        if camera_index == -1:
            messagebox.showerror("Erreur", "Aucune caméra détectée.")
            window.destroy()
            return

        face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + "haarcascade_frontalface_default.xml")
        cap = cv2.VideoCapture(camera_index)

        def show_frame():
            ret, frame = cap.read()
            if not ret:
                return

            faces = face_cascade.detectMultiScale(frame, 1.3, 5)
            for (x, y, w, h) in faces:
                cv2.rectangle(frame, (x, y), (x + w, y + h), (0, 255, 0), 2)
                capture_ready[0] = True

            img = cv2.cvtColor(frame, cv2.COLOR_BGR2RGBA)
            img = cv2.resize(img, (600, 400))
            photo = ImageTk.PhotoImage(image=Image.fromarray(img))
            lmain.configure(image=photo)
            lmain.image = photo
            window.after(10, show_frame)

        def capture_photo():
            if not capture_ready[0]:
                messagebox.showwarning("Alerte", "Aucun visage bien cadré détecté.")
                return

            ret, frame = cap.read()
            if not ret:
                messagebox.showerror("Erreur", "Échec de la capture.")
                return

            faces = face_cascade.detectMultiScale(frame, 1.3, 5)
            for (x, y, w, h) in faces:
                face_img = frame[y:y + h, x:x + w]
                resized_face = cv2.resize(face_img, (150, 150))
                filename = f"etudiant_{id_etudiant}.jpg"
                path = os.path.join(folder, filename)
                cv2.imwrite(path, resized_face)

                try:
                    conn = mysql.connector.connect(host="localhost", user="root", password="", database="unisecureid_db")
                    cursor = conn.cursor()
                    cursor.execute("UPDATE etudiants SET photo = %s WHERE id_etudiant = %s", (filename, id_etudiant))
                    conn.commit()
                    conn.close()
                    messagebox.showinfo("Succès", "Photo enregistrée avec succès.")
                    cap.release()
                    window.destroy()
                except Exception as e:
                    messagebox.showerror("Erreur DB", str(e))
                break

        Button(window, text="📸 Capturer le visage", command=capture_photo,
               bg=COLOR_BORD, fg="white", font=("Helvetica", 13)).pack(pady=10)
        show_frame()

    def enregistrer_etudiant():
        nom = nom_entry.get().strip()
        sexe = sexe_combo.get()
        telephone = tel_entry.get().strip()
        matricule = matricule_var.get()
        departement = dep_entry.get().strip()
        option = opt_entry.get().strip()
        date_enregistrement = datetime.now().strftime("%Y-%m-%d %H:%M:%S")

        if not all([nom, sexe, telephone, departement, option]):
            messagebox.showwarning("Champs requis", "Tous les champs doivent être remplis.")
            return

        try:
            conn = mysql.connector.connect(host="localhost", user="root", password="", database="unisecureid_db")
            cursor = conn.cursor()
            cursor.execute("""
                INSERT INTO etudiants (nomcomplet, sexe, telephone, matricule, departement, `options`, date_enregistrement, photo)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
            """, (nom, sexe, telephone, matricule, departement, option, date_enregistrement, "pending.jpg"))
            conn.commit()
            id_etudiant = cursor.lastrowid
            conn.close()

            id_etudiant_holder["value"] = id_etudiant
            messagebox.showinfo("Succès", "Étudiant enregistré. Passez à la capture du visage.")
            capture_etudiant_photo()
        except Exception as e:
            messagebox.showerror("Erreur MySQL", str(e))

    Button(root, text="✅ Enregistrer les données et capturer", command=enregistrer_etudiant,
           bg=COLOR_BORD, fg="white", font=("Helvetica", 13)).pack(pady=20)
