
from argparse import Action
from Admin_User_Management_Function import open_user_management  # type: ignore
from Staff_Management_Function import open_staff_management  # type: ignore
from open_statistics import open_statistics_dashboard # type: ignore
from Agent_Etudiant_Registration import open_etudiant_registration  # type: ignore


import tkinter as tk
from tkinter import ttk, messagebox
from PIL import Image, ImageTk  # type: ignore
import mysql.connector  # type: ignore
import subprocess
import sys

COLOR_BORD = "#800000"
FONT_TITLE = ("Helvetica", 28, "bold")
FONT_PERCENT = ("Helvetica", 24, "bold")

class CircularProgressBar(tk.Canvas):
    def __init__(self, parent, size=200, width=15, max_value=100, **kwargs):
        super().__init__(parent, width=size, height=size, bg="white", highlightthickness=0, **kwargs)
        self.size = size
        self.width = width
        self.max_value = max_value
        self.arc = None
        self.percent_text = None
        self.create_oval(width, width, size-width, size-width, outline="#ddd", width=width)
        self.arc = self.create_arc(width, width, size-width, size-width, start=90, extent=0,
                                   style=tk.ARC, outline=COLOR_BORD, width=width)
        self.percent_text = self.create_text(size//2, size//2, text="0%", font=FONT_PERCENT, fill=COLOR_BORD)

    def update_progress(self, value):
        angle = (value / self.max_value) * -360
        percent = f"{value}%"
        self.itemconfig(self.arc, extent=angle)
        self.itemconfig(self.percent_text, text=percent)

def verify_credentials(username, password):
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="unisecureid_db"
        )
        cursor = conn.cursor()
        cursor.execute("SELECT role, nom_complet, status FROM users WHERE username = %s AND password = %s", (username, password))
        result = cursor.fetchone()
        conn.close()
        return result
    except Exception as e:
        messagebox.showerror("Erreur Base de Données", str(e))
        return None

def launch_presence_recognition():
    try:
        subprocess.Popen([sys.executable, "Staff_Presence_FaceRecognition.py"])
    except Exception as e:
        messagebox.showerror("Erreur", f"Impossible de lancer la reconnaissance : {e}")

def open_dashboard(role, nom_complet):
    dash = tk.Tk()
    dash.title("Tableau de Bord - UniSecure ID")
    dash.state('zoomed')
    dash.configure(bg="white")

    tk.Label(dash, text=f"Bienvenue {nom_complet} ({role.upper()})", font=FONT_TITLE, bg="white", fg=COLOR_BORD).pack(pady=40)

    # Liste des actions selon le rôle
    if role == "admin":
        actions = [
            "📋 Gérer le personnel de l'institution",
            "🕵️‍♂️ Lancer la camera pour la présence du personnel (Reconnaissance Faciale)",
            "🕵️‍♂️ Lancer la camera pour la sortie du personnel (Reconnaissance Faciale)",
            "📊 Voir les statistiques"
        ]
    elif role == "agent":
        actions = [
            "📝 Enregistrer un étudiant",
            "📋 Lancer la camera pour les présences du personnel",
             "🕵️‍♂️ Lancer la camera pour la sortie du personnel (Reconnaissance Faciale)",
            "🕵️‍♂️ Lancer la camera pour la présence des étudiants (Reconnaissance Faciale)",
             "🕵️‍♂️ Lancer la camera pour la sortie des étudiants (Reconnaissance Faciale)"
           
        ]
    # ... autres rôles ...

    # ✅ Fonction appelée lors d’un clic sur un bouton d’action
    def handle_action(action):
        try:
            if action == "🧑‍💼 Gérer les utilisateurs":
                open_user_management()
            elif action == "📋 Gérer le personnel de l'institution":
                open_staff_management()
            elif action == "🕵️‍♂️ Lancer la camera pour la présence du personnel (Reconnaissance Faciale)":
                launch_presence_recognition()
           
            elif action == "📊 Voir les statistiques":
                open_statistics_dashboard()
            elif action == "📝 Enregistrer un étudiant":
                open_etudiant_registration()
            else:
                messagebox.showinfo("Info", f"La fonction pour '{action}' n'est pas encore disponible.")
        except Exception as e:
            messagebox.showerror("Erreur", f"Une erreur est survenue : {e}")

    # ✅ Affichage des boutons d’action (doit être à l'intérieur de open_dashboard)
    for act in actions:
        tk.Button(dash, text=act, bg=COLOR_BORD, fg="white", font=("Helvetica", 14),
                  command=lambda a=act: handle_action(a)).pack(pady=8)

    # Boutons en bas
    def logout():
        dash.destroy()
        show_login()

    tk.Button(dash, text="Déconnexion", command=logout, bg="red", fg="white", font=("Helvetica", 14)).pack(pady=30)
    tk.Button(dash, text="Quitter", command=dash.destroy, bg="gray", fg="white", font=("Helvetica", 14)).pack()

    dash.mainloop()



def show_login():
    login = tk.Tk()
    login.title("Connexion - UniSecure ID")
    login.state('zoomed')
    login.configure(bg="white")

    try:
        login_logo = Image.open("logo.png").resize((200, 200))
    except:
        from PIL import ImageDraw
        login_logo = Image.new("RGB", (200, 200), COLOR_BORD)
        draw = ImageDraw.Draw(login_logo)
        draw.text((50, 90), "LOGO", fill="white")

    login_logo_img = ImageTk.PhotoImage(login_logo)
    tk.Label(login, image=login_logo_img, bg="white").pack(pady=10)
    tk.Label(login, text="Connexion", font=FONT_TITLE, fg=COLOR_BORD, bg="white").pack(pady=20)

    tk.Label(login, text="Nom d'utilisateur :", font=("Helvetica", 16), bg="white").pack()
    username_entry = tk.Entry(login, font=("Helvetica", 14))
    username_entry.pack()

    tk.Label(login, text="Mot de passe :", font=("Helvetica", 16), bg="white").pack()
    password_entry = tk.Entry(login, show="*", font=("Helvetica", 14))
    password_entry.pack()

    def attempt_login():
        credentials = verify_credentials(username_entry.get(), password_entry.get())
        if credentials:
            role, nom_complet, statut = credentials
            if statut.lower() == "activer":
                login.destroy()
                open_dashboard(role, nom_complet)
            else:
                messagebox.showwarning("Accès refusé", "Votre compte est désactivé. Contactez l'administrateur.")
        else:
            messagebox.showerror("Erreur", "Nom d'utilisateur ou mot de passe incorrect.")

    tk.Button(login, text="Se connecter", command=attempt_login,
              bg=COLOR_BORD, fg="white", font=("Helvetica", 14)).pack(pady=30)
    login.mainloop()

def start_progress():
    splash = tk.Tk()
    splash.title("Chargement - UniSecure ID")
    splash.state('zoomed')
    splash.configure(bg="white")

    try:
        logo_img = Image.open("logo.png").resize((120, 120))
    except:
        logo_img = Image.new("RGB", (120, 120), COLOR_BORD)
    logo_photo = ImageTk.PhotoImage(logo_img)

    tk.Label(splash, image=logo_photo, bg="white").pack(pady=10)
    tk.Label(splash, text="Bienvenue dans UniSecure ID", font=FONT_TITLE, fg=COLOR_BORD, bg="white").pack(pady=10)

    circular_bar = CircularProgressBar(splash, size=250, width=18)
    circular_bar.pack(pady=40)

    for i in range(101):
        splash.after(20)
        circular_bar.update_progress(i)
        splash.update()
    splash.destroy()
    show_login()

start_progress()
