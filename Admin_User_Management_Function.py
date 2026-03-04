
from tkinter import *
from tkinter import ttk, messagebox
from PIL import Image, ImageTk
import mysql.connector

COLOR_BORD = "#800000"
FONT_TITLE = ("Helvetica", 22, "bold")

def open_user_management():
    win = Toplevel()
    win.title("Gestion des Utilisateurs")
    win.state('zoomed')
    win.configure(bg="white")

    try:
        logo_img = Image.open("logo.png").resize((100, 100))
        logo = ImageTk.PhotoImage(logo_img)
        Label(win, image=logo, bg="white").pack(pady=5)
        win.logo = logo
    except:
        Label(win, text="UniSecure ID", font=("Helvetica", 20, "bold"), fg=COLOR_BORD, bg="white").pack(pady=10)

    Label(win, text="🧑‍💼 Enregistrement / Gestion des utilisateurs", font=FONT_TITLE, bg="white", fg=COLOR_BORD).pack(pady=5)

    columns = ("id", "nom_complet", "username", "role", "status")
    tree = ttk.Treeview(win, columns=columns, show="headings")
    for col in columns:
        tree.heading(col, text=col.capitalize())
        tree.column(col, anchor=CENTER, width=140)
    tree.pack(pady=10, fill="x", padx=40)

    def refresh_table():
        for row in tree.get_children():
            tree.delete(row)
        try:
            conn = mysql.connector.connect(
                host="localhost", user="root", password="", database="unisecureid_db"
            )
            cursor = conn.cursor()
            cursor.execute("SELECT id, nom_complet, username, role, status FROM users")
            for row in cursor.fetchall():
                tree.insert("", "end", values=row)
            conn.close()
        except Exception as e:
            messagebox.showerror("Erreur", str(e))

    def toggle_status():
        selected = tree.focus()
        if not selected:
            messagebox.showwarning("Aucun utilisateur", "Veuillez sélectionner un utilisateur.")
            return
        values = tree.item(selected, 'values')
        user_id, nom, username, role, status = values
        new_status = "Désactiver" if status == "Activer" else "Activer"
        try:
            conn = mysql.connector.connect(
                host="localhost", user="root", password="", database="unisecureid_db"
            )
            cursor = conn.cursor()
            cursor.execute("UPDATE users SET status = %s WHERE id = %s", (new_status, user_id))
            conn.commit()
            conn.close()
            refresh_table()
            messagebox.showinfo("Succès", f"Statut de {username} mis à jour.")
        except Exception as e:
            messagebox.showerror("Erreur", str(e))

    def open_registration_form():
        reg = Toplevel(win)
        reg.title("Ajouter un utilisateur")
        reg.geometry("500x500")
        reg.configure(bg="white")

        Label(reg, text="Formulaire d'enregistrement", font=FONT_TITLE, fg=COLOR_BORD, bg="white").pack(pady=10)

        def styled_entry(label_text):
            Label(reg, text=label_text, font=("Helvetica", 13), bg="white").pack()
            entry = Entry(reg, font=("Helvetica", 13), bd=2, relief=GROOVE)
            entry.pack(pady=3, fill=X, padx=50)
            return entry

        nom_entry = styled_entry("Nom complet :")
       
        user_entry = styled_entry("Nom d'utilisateur :")
        pwd_entry = styled_entry("Mot de passe :")
        pwd_entry.config(show="*")

        Label(reg, text="Rôle :", font=("Helvetica", 13), bg="white").pack()
        role_var = StringVar()
        role_menu = ttk.Combobox(reg, textvariable=role_var, font=("Helvetica", 13), state="readonly")
        role_menu['values'] = ("admin", "agent", "enseignant", "etudiant", "financier")
        role_menu.current(0)
        role_menu.pack(pady=5, fill=X, padx=50)

        def enregistrer():
            nom = nom_entry.get()
           
            username = user_entry.get()
            password = pwd_entry.get()
            role = role_var.get()

            if not (nom and username and password and role):
                messagebox.showwarning("Champs requis", "Tous les champs doivent être remplis.")
                return
            try:
                conn = mysql.connector.connect(
                    host="localhost", user="root", password="", database="unisecureid_db"
                )
                cursor = conn.cursor()
                cursor.execute("INSERT INTO users (nom_complet, username, password, role, status) VALUES (%s, %s, %s, %s, %s)",
                               (nom, username, password, role, "activer"))
                conn.commit()
                conn.close()
                refresh_table()
                reg.destroy()
                messagebox.showinfo("Succès", "Utilisateur ajouté avec succès.")
            except Exception as e:
                messagebox.showerror("Erreur", str(e))

        Button(reg, text="✅ Enregistrer", command=enregistrer, bg=COLOR_BORD, fg="white", font=("Helvetica", 13)).pack(pady=20)

    btn_frame = Frame(win, bg="white")
    btn_frame.pack(pady=10)
    Button(btn_frame, text="➕ Ajouter un utilisateur", command=open_registration_form, bg=COLOR_BORD, fg="white", font=("Helvetica", 12)).grid(row=0, column=0, padx=10)
    Button(btn_frame, text="🔄 Activer/Désactiver", command=toggle_status, bg="gray", fg="white", font=("Helvetica", 12)).grid(row=0, column=1, padx=10)

    refresh_table()
